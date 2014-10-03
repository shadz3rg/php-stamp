<?php

namespace OfficeML\Document\WordDocument\Expression;

use OfficeML\Exception\ExpressionException;
use OfficeML\Expression;
use OfficeML\Processor;
use OfficeML\XMLHelper;

class Cell implements Expression
{
    public function insertTemplateLogic(array $arguments, \DOMNode $node, \DOMDocument $template)
    {
        if (count($arguments) !== 1) {
            throw new ExpressionException('Wrong arguments number, 1 needed, got ' . count($arguments));
        }

        list($rowName) = $arguments;

        // find existing or initiate new table row template
        if ($this->isRowTemplateExist($rowName, $template) === false) {

            $rowTemplate = $template->createElementNS(Processor::XSL_NS, 'xsl:template');
            $rowTemplate->setAttribute('name', $rowName);

            // find row node
            $rowNode = XMLHelper::parentUntil('w:tr', $node);

            // call-template for each row
            $foreachNode = $template->createElementNS(Processor::XSL_NS, 'xsl:for-each');
            $foreachNode->setAttribute('select', '/' . Processor::VALUES_PATH . '/' . $rowName . '/item');
            $callTemplateNode = $template->createElementNS(Processor::XSL_NS, 'xsl:call-template');
            $callTemplateNode->setAttribute('name', $rowName);
            $foreachNode->appendChild($callTemplateNode);

            // insert call-template before moving
            $rowNode->parentNode->insertBefore($foreachNode, $rowNode);

            // move node into row template
            $rowTemplate->appendChild($rowNode);
            $template->documentElement->appendChild($rowTemplate);
        }

        $placeholder = $template->createElementNS(Processor::XSL_NS, 'xsl:value-of');
        $placeholder->setAttribute('select', $rowName);

        $node->appendChild($placeholder);
        return $node;
    }

    private function isRowTemplateExist($rowName, \DOMDocument $template)
    {
        $xpath = new \DOMXPath($template);
        $nodeList = $xpath->query('/xsl:stylesheet/xsl:template[@name="' . $rowName . '"]');

        if ($nodeList->length > 1) {
            throw new ExpressionException('Unexpected template count.');
        }

        return ($nodeList->length === 1);
    }
} 