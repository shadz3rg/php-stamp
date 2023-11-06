<?php

namespace PHPStamp;

use PHPStamp\Exception\XmlException;

class Processor
{
    /**
     * XSL Namespace.
     */
    public const XSL_NS = 'http://www.w3.org/1999/XSL/Transform';

    /**
     * Root node for values XML document.
     */
    public const VALUE_NODE = 'values';

    /**
     * Wrap document content into XSL template.
     *
     * @return void
     */
    public static function wrapIntoTemplate(\DOMDocument $document)
    {
        $stylesheet = $document->createElementNS(self::XSL_NS, 'xsl:stylesheet');
        $stylesheet->setAttribute('version', '1.0');

        $output = $document->createElementNS(self::XSL_NS, 'xsl:output');
        $output->setAttribute('method', 'xml');
        $output->setAttribute('encoding', 'UTF-8');
        $stylesheet->appendChild($output);

        $template = $document->createElementNS(self::XSL_NS, 'xsl:template');
        $template->setAttribute('match', '/'.self::VALUE_NODE);

        $root = $document->documentElement;
        if ($root === null) {
            throw new XmlException('Root node expected');
        }

        $template->appendChild($root);
        $stylesheet->appendChild($template);

        $document->appendChild($stylesheet);
    }

    /**
     * Split node into three parts (before [[tag]] after) and replace tag with XSL logic.
     *
     * @param string      $search placeholder tag with brackets
     * @param string      $path   XPath to value in the encoded XML document
     * @param \DOMElement $node   node with placeholder
     *
     * @throws XmlException
     */
    public static function insertTemplateLogic(string $search, string $path, \DOMElement $node): bool
    {
        $template = $node->ownerDocument;
        if ($template === null) {
            throw new XmlException('Detached node');
        }

        $node->setAttribute('xml:space', 'preserve'); // fix whitespaces in mixed node

        /** @var \DOMText $textNode */
        foreach ($node->childNodes as $textNode) {
            $nodeValue = $textNode->nodeValue; // utf8_decode
            if ($nodeValue === null) {
                continue;
            }

            // before [[tag]] after
            /** @var array<string> $nodeValueParts */
            $nodeValueParts = explode($search, $nodeValue, 2); // fix similar tags in one node

            if (count($nodeValueParts) === 2) {
                $textNode->nodeValue = ''; // reset

                // text before
                $before = $template->createTextNode($nodeValueParts[0]);
                $node->insertBefore($before, $textNode);

                // add xsl logic
                $placeholder = $template->createElementNS(self::XSL_NS, 'xsl:value-of');
                $placeholder->setAttribute('select', $path);
                $node->insertBefore($placeholder, $textNode);

                // text after
                $after = $template->createTextNode($nodeValueParts[1]);
                $node->insertBefore($after, $textNode);

                $node->removeChild($textNode);

                return true;
            }
        }

        return false;
    }

    /**
     * Word XML can contain curly braces in attributes, which conflicts with XSL logic.
     * Escape them before template created.
     *
     * @throws XmlException
     */
    public static function escapeXsl(\DOMDocument $document): void
    {
        $xpath = new \DOMXPath($document);

        // escape attr for xsl
        $nodeList = $xpath->query('//*[contains(@uri, "{") and contains(@uri ,"}")]');
        if ($nodeList === false) {
            throw new XmlException('Malformed query');
        }

        /** @var \DOMNode $node */
        foreach ($nodeList as $node) {
            if ($node->attributes === null) {
                continue;
            }

            /** @var \DOMAttr $attr */
            foreach ($node->attributes as $attr) {
                if ($attr->nodeValue === null) {
                    continue;
                }

                $attr->nodeValue = str_replace(['{', '}'], ['{{', '}}'], $attr->nodeValue);
            }
        }
    }

    /**
     * Word XML can contain curly braces in attributes, which conflicts with XSL logic.
     * Undo escape them after template conversion.
     */
    public static function undoEscapeXsl(\DOMDocument $document): void
    {
        $xpath = new \DOMXPath($document);

        // escape attr for xsl
        $nodeList = $xpath->query('//*[contains(@uri, "{{") and contains(@uri ,"}}")]');
        if ($nodeList === false) {
            throw new XmlException('Malformed query');
        }

        /** @var \DOMNode $node */
        foreach ($nodeList as $node) {
            if ($node->attributes === null) {
                continue;
            }

            /** @var \DOMAttr $attr */
            foreach ($node->attributes as $attr) {
                if ($attr->nodeValue === null) {
                    continue;
                }

                $attr->nodeValue = str_replace(['{{', '}}'], ['{', '}'], $attr->nodeValue);
            }
        }
    }
}
