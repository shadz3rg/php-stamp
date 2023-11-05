<?php

namespace PHPStamp\Document\WordDocument\Extension;

use PHPStamp\Exception\ExtensionException;
use PHPStamp\Exception\XmlException;
use PHPStamp\Extension\Extension;
use PHPStamp\Processor;
use PHPStamp\XMLHelper;

class Cell extends Extension
{
    /**
     * @param array<string> $arguments
     *
     * @return array<string>
     *
     * @throws ExtensionException
     */
    protected function prepareArguments(array $arguments): array
    {
        if (count($arguments) === 0) {
            throw new ExtensionException('At least 1 argument required.');
        }

        return $arguments;
    }

    /**
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
        if ($template === null) {
            throw new XmlException('Detached node');
        }

        $root = $template->documentElement;
        if ($root === null) {
            throw new XmlException('Root node not found');
        }

        // find existing or initiate new table row template
        if ($this->isRowTemplateExist($explicitName, $template) === false) {
            $rowTemplate = $template->createElementNS(Processor::XSL_NS, 'xsl:template');
            $rowTemplate->setAttribute('name', $explicitName);

            // find row node
            $rowNode = XMLHelper::parentUntil('w:tr', $node);
            if ($rowNode === null) {
                throw new ExtensionException('Cant find row node');
            }

            $containerNode = $rowNode->parentNode;
            if ($containerNode === null) {
                throw new ExtensionException('Cant find container node');
            }

            // call-template for each row
            $foreachNode = $template->createElementNS(Processor::XSL_NS, 'xsl:for-each');
            $foreachNode->setAttribute('select', '/'.Processor::VALUE_NODE.'/'.$rowName.'/item');
            $callTemplateNode = $template->createElementNS(Processor::XSL_NS, 'xsl:call-template');
            $callTemplateNode->setAttribute('name', $explicitName);
            $foreachNode->appendChild($callTemplateNode);

            // insert call-template before moving
            $containerNode->insertBefore($foreachNode, $rowNode);

            // move node into row template
            $rowTemplate->appendChild($rowNode);
            $root->appendChild($rowTemplate);
        }

        $relativePath = $this->tag->getRelativePath();
        if ($relativePath === null) {
            throw new ExtensionException('Tag path is empty');
        }

        Processor::insertTemplateLogic($this->tag->getTextContent(), $relativePath, $node);
    }

    /**
     * @throws ExtensionException
     * @throws XmlException
     */
    private function isRowTemplateExist(string $rowName, \DOMDocument $template): bool
    {
        $xpath = new \DOMXPath($template);

        $nodeList = $xpath->query('/xsl:stylesheet/xsl:template[@name="'.$rowName.'"]');
        if ($nodeList === false) {
            throw new XmlException('Malformed query');
        }

        if ($nodeList->length > 1) {
            throw new ExtensionException('Unexpected template count.');
        }

        return $nodeList->length === 1;
    }
}
