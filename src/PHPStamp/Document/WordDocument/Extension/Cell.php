<?php

namespace PHPStamp\Document\WordDocument\Extension;

use PHPStamp\Exception\ExtensionException;
use PHPStamp\Extension\Extension;
use PHPStamp\Processor;
use PHPStamp\XMLHelper;

class Cell extends Extension
{
    /**
     * @param array $arguments
     * @return array
     * @throws ExtensionException
     */
    protected function prepareArguments(array $arguments)
    {
        if (count($arguments) === 0) {
            throw new ExtensionException('At least 1 argument required.');
        }

        return $arguments;
    }

    /**
     * @param array $arguments
     * @param \DOMElement $node
     * @throws ExtensionException
     * @throws \PHPStamp\Exception\ParsingException
     */
    protected function insertTemplateLogic(array $arguments, \DOMElement $node)
    {
        $rowName = $arguments[0];

        $explicitName = $rowName;
        if (count($arguments) === 2) {
            $explicitName = $arguments[1];
        }

        $template = $node->ownerDocument;

        // find existing or initiate new table row template
        if ($this->isRowTemplateExist($explicitName, $template) === false) {

            $rowTemplate = $template->createElementNS(Processor::XSL_NS, 'xsl:template');
            $rowTemplate->setAttribute('name', $explicitName);

            // find row node
            $rowNode = XMLHelper::parentUntil('w:tr', $node);

            // call-template for each row
            $foreachNode = $template->createElementNS(Processor::XSL_NS, 'xsl:for-each');
            $foreachNode->setAttribute('select', '/' . Processor::VALUE_NODE . '/' . $rowName . '/item');
            $callTemplateNode = $template->createElementNS(Processor::XSL_NS, 'xsl:call-template');
            $callTemplateNode->setAttribute('name', $explicitName);
            $foreachNode->appendChild($callTemplateNode);

            // insert call-template before moving
            $rowNode->parentNode->insertBefore($foreachNode, $rowNode);

            // move node into row template
            $rowTemplate->appendChild($rowNode);
            $template->documentElement->appendChild($rowTemplate);
        }

        $relativePath = $this->tag->getRelativePath();
        Processor::insertTemplateLogic($this->tag->getTextContent(), $relativePath, $node);
    }

    /**
     * @param $rowName
     * @param \DOMDocument $template
     * @return bool
     * @throws ExtensionException
     */
    private function isRowTemplateExist($rowName, \DOMDocument $template)
    {
        $xpath = new \DOMXPath($template);
        $nodeList = $xpath->query('/xsl:stylesheet/xsl:template[@name="' . $rowName . '"]');

        if ($nodeList->length > 1) {
            throw new ExtensionException('Unexpected template count.');
        }

        return $nodeList->length === 1;
    }
}