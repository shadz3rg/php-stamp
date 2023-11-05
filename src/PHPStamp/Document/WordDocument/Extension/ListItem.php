<?php

namespace PHPStamp\Document\WordDocument\Extension;

use PHPStamp\Exception\ExtensionException;
use PHPStamp\Exception\XmlException;
use PHPStamp\Extension\Extension;
use PHPStamp\Processor;
use PHPStamp\XMLHelper;

class ListItem extends Extension
{
    /**
     * @inherit
     *
     * @throws ExtensionException
     */
    protected function prepareArguments(array $arguments): array
    {
        if (count($arguments) !== 0) {
            throw new ExtensionException('Wrong arguments number, 0 needed, got '.count($arguments));
        }

        return $arguments;
    }

    /**
     * @inherit
     */
    protected function insertTemplateLogic(array $arguments, \DOMElement $node)
    {
        $template = $node->ownerDocument;
        if ($template === null) {
            throw new XmlException('Detached node');
        }

        $root = $template->documentElement;
        if ($root === null) {
            throw new XmlException('Root node not found');
        }

        $listName = $this->tag->getRelativePath();
        if ($listName === null) {
            throw new ExtensionException('Tag path is empty');
        }

        // find existing or initiate new table row template
        if ($this->isListItemTemplateExist($listName, $template) === false) {
            $rowTemplate = $template->createElementNS(Processor::XSL_NS, 'xsl:template');
            $rowTemplate->setAttribute('name', $listName);

            // find row node
            $rowNode = XMLHelper::parentUntil('w:p', $node);
            if ($rowNode === null) {
                throw new ExtensionException('Cant find row node');
            }

            $containerNode = $rowNode->parentNode;
            if ($containerNode === null) {
                throw new ExtensionException('Cant find container node');
            }

            // call-template for each row
            $foreachNode = $template->createElementNS(Processor::XSL_NS, 'xsl:for-each');
            $foreachNode->setAttribute('select', '/'.Processor::VALUE_NODE.'/'.$listName.'/item');
            $callTemplateNode = $template->createElementNS(Processor::XSL_NS, 'xsl:call-template');
            $callTemplateNode->setAttribute('name', $listName);
            $foreachNode->appendChild($callTemplateNode);

            // insert call-template before moving
            $containerNode->insertBefore($foreachNode, $rowNode);

            // move node into row template
            $rowTemplate->appendChild($rowNode);
            $root->appendChild($rowTemplate);
        }

        // FIXME пофиксить повторное использование функции
        Processor::insertTemplateLogic($this->tag->getTextContent(), '.', $node);
    }

    /**
     * @throws ExtensionException
     * @throws XmlException
     */
    private function isListItemTemplateExist(string $rowName, \DOMDocument $template): bool
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
