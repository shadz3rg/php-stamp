<?php
namespace PHPStamp;


class Processor
{
    /**
     * XSL Namespace
     */
    const XSL_NS = 'http://www.w3.org/1999/XSL/Transform';

    /**
     * Root node for values XML document.
     */
    const VALUE_NODE = 'values';

    /**
     * Wrap document content into XSL template.
     *
     * @param \DOMDocument $document
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
        $template->setAttribute('match', '/' . self::VALUE_NODE);
        $template->appendChild($document->documentElement);
        $stylesheet->appendChild($template);

        $document->appendChild($stylesheet);
    }

    /**
     * Split node into three parts (before [[tag]] after) and replace tag with XSL logic.
     *
     * @param string $search Placeholder tag with brackets.
     * @param string $path XPath to value in the encoded XML document.
     * @param \DOMElement $node Node with placeholder.
     * @return bool
     */
    public static function insertTemplateLogic($search, $path, \DOMElement $node)
    {
        $template = $node->ownerDocument;

        $node->setAttribute('xml:space', 'preserve'); // fix whitespaces in mixed node

        /** @var $textNode \DOMText */
        foreach ($node->childNodes as $textNode) {
            $nodeValue = $textNode->nodeValue; // utf8_decode

            // before [[tag]] after
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
     * @param \DOMDocument $document
     */
    public static function escapeXsl(\DOMDocument $document)
    {
        $xpath = new \DOMXPath($document);
        // escape attr for xsl
        $nodeList = $xpath->query('//*[contains(@uri, "{") and contains(@uri ,"}")]');
        /** @var $node \DOMNode  */
        foreach ($nodeList as $node) {
            /** @var $attr \DOMAttr  */
            foreach ($node->attributes as $attr) {
                $attr->nodeValue = str_replace(array('{', '}'), array('{{', '}}'), $attr->nodeValue);
            }
        }
    }

    /**
     * Word XML can contain curly braces in attributes, which conflicts with XSL logic.
     * Undo escape them after template conversion.
     *
     * @param \DOMDocument $document
     */
    public static function undoEscapeXsl(\DOMDocument $document)
    {
        $xpath = new \DOMXPath($document);
        // escape attr for xsl
        $nodeList = $xpath->query('//*[contains(@uri, "{{") and contains(@uri ,"}}")]');
        /** @var $node \DOMNode  */
        foreach ($nodeList as $node) {
            /** @var $attr \DOMAttr  */
            foreach ($node->attributes as $attr) {
                $attr->nodeValue = str_replace(array('{{', '}}'), array('{', '}'), $attr->nodeValue);
            }
        }
    }
}