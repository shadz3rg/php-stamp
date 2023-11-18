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
     * @throws XmlException
     * @throws \DOMException
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

        $documentRoot = $document->documentElement;
        if ($documentRoot === null) {
            throw new XmlException('Root node expected');
        }

        $template->appendChild($documentRoot);
        $stylesheet->appendChild($template);

        $document->appendChild($stylesheet);

        // Common templates
        $xml = /** @lang text */
            <<<EOT
<xsl:template name="print" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:w="https://schemas.openxmlformats.org/wordprocessingml/2006/main">
    <xsl:param name="text" select="."/>
    <xsl:param name="delimiter" select="'  '"/>

    <xsl:if test="string-length(\$text)">
        <xsl:if test="not(\$text=.)">
            <w:br/>
        </xsl:if>
        <w:t xml:space="preserve"><xsl:value-of select="substring-before(concat(\$text, \$delimiter), \$delimiter)"/></w:t>
        <xsl:call-template name="print">
            <xsl:with-param name="text" select="substring-after(\$text, \$delimiter)"/>
        </xsl:call-template>
    </xsl:if>
</xsl:template>
EOT;
        $f = $document->createDocumentFragment();
        $f->appendXML($xml);


        $stylesheet->appendChild($f);

        var_dump($document->saveXML());
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
        /** @var \DOMDocument $template */
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

            // before [[tag]] after -> ['before ', '[[tag]]', ' after']

            /** @var array<string> $nodeValueParts */
            $nodeValueParts = explode($search, $nodeValue, 2); // fix similar tags in one node
            if (count($nodeValueParts) === 2) {
                $textNode->nodeValue = $nodeValueParts[0];

                // text before
                //$before = $template->createTextNode($nodeValueParts[0]);
                //$node->insertBefore($before, $textNode);

                // add xsl logic
                $middle = $template->createElementNS(self::XSL_NS, 'xsl:call-template');
                $middle->setAttribute('name', 'print');



                // text after
                $after = $template->createElementNS('https://schemas.openxmlformats.org/wordprocessingml/2006/main', 'w:t', $nodeValueParts[1]);
                $after->setAttribute('xml:space', 'preserve');
                //$node->insertBefore($after, $textNode);

                // $node->removeChild($textNode);

                $node->parentNode->insertBefore($after, $node->nextSibling);
                $node->parentNode->insertBefore($middle, $after);

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
