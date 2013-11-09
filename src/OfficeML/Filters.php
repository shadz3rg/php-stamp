<?php
namespace OfficeML;

class Filters
{
    public static $filters;
}

Filters::$filters['cell'] = function(array $token, $arg = null, \DOMNode $node, \DOMDocument $document, \DOMXPath $xpath) {
    $template = $document->createElementNS(self::XSL_NS, 'xsl:template');
    $template->setAttribute('match', '//tokens');
    $template->appendChild($document->documentElement);
    //$stylesheet->appendChild($template);

    $tableRow = $xpath->query('//w:tr[contains(., "' . $token['value'] . '")]');
    $token['value'] = $token['value'] . '_mod';
    return $token;
};