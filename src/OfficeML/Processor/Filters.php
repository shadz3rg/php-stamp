<?php
namespace OfficeML\Processor;

use OfficeML\Processor;
use OfficeML\Exception\TokenException;

class Filters
{
    public static $filters;
}

Filters::$filters['cell'] = function(Token $token, \DOMNode $textNode, \DOMDocument $template, \DOMXPath $xpath) {
    list($row, $field) = explode('>', $token->getValue());

    // Find existing or initiate new table row template
    $rowTemplateQuery = $xpath->query('/xsl:stylesheet/xsl:template[@name="' . $row . '"]');
    if ($rowTemplateQuery->length === 0) {
        $rowTemplate = $template->createElementNS(Processor::XSL_NS, 'xsl:template');
        $rowTemplate->setAttribute('name', $row);

        // ParentUntil()
        $parent = $textNode->parentNode;
        while ($parent->nodeName !== 'w:tr') {
            $parent = $parent->parentNode;
            if ($parent === null) {
                throw new TokenException('Row not found.');
            }
        }

        // $rowNode = $parent->cloneNode(true);
        $rowNode = $parent;

        // call-template for each row
        $foreach = $template->createElementNS(Processor::XSL_NS, 'xsl:for-each');
        $foreach->setAttribute('select', '//tokens/' . $row . '/item');
        $callTemplate = $template->createElementNS(Processor::XSL_NS, 'xsl:call-template');
        $callTemplate->setAttribute('name', $row);
        $foreach->appendChild($callTemplate);

        // Insert call-template before moving
        $rowNode->parentNode->insertBefore($foreach, $rowNode);

        // Move node!
        $rowTemplate->appendChild($rowNode);
        $template->documentElement->appendChild($rowTemplate);
    }

    $placeholder = $template->createElementNS(Processor::XSL_NS, 'xsl:value-of');
    $placeholder->setAttribute('select', $field);

    $textNode->appendChild($placeholder);
    return $token;
};