<?php

namespace PHPStamp\Document\WordDocument;

use PHPStamp\Exception\XmlException;

class LineBreaks
{
    /**
     * Replace user-provided line breaks in Word text nodes with explicit Word line break nodes.
     *
     * @throws XmlException
     */
    public static function replace(\DOMDocument $document): void
    {
        $nodes = [];
        foreach ($document->getElementsByTagName('t') as $node) {
            if ($node->prefix !== 'w') {
                continue;
            }

            if (strpos($node->textContent, PHP_EOL) !== false) {
                $nodes[] = $node;
            }
        }

        foreach ($nodes as $node) {
            self::replaceInTextNode($node);
        }
    }

    /**
     * @throws XmlException
     */
    private static function replaceInTextNode(\DOMElement $node): void
    {
        $parent = $node->parentNode;
        if ($parent === null) {
            throw new XmlException('Detached node');
        }

        $ownerDocument = $node->ownerDocument;
        if ($ownerDocument === null) {
            throw new XmlException('Detached node');
        }

        $namespace = $node->namespaceURI;
        if ($namespace === null) {
            throw new XmlException('Text node namespace expected');
        }

        $parts = explode(PHP_EOL, $node->textContent);
        $firstPart = array_shift($parts);
        $node->textContent = $firstPart;
        $node->setAttribute('xml:space', 'preserve');

        $insertAfter = $node;
        foreach ($parts as $part) {
            $break = $ownerDocument->createElementNS($namespace, 'w:br');
            $text = $ownerDocument->createElementNS($namespace, 'w:t');
            $text->setAttribute('xml:space', 'preserve');
            $text->appendChild($ownerDocument->createTextNode($part));

            self::insertAfter($parent, $break, $insertAfter);
            self::insertAfter($parent, $text, $break);
            $insertAfter = $text;
        }
    }

    private static function insertAfter(\DOMNode $parent, \DOMNode $node, \DOMNode $insertAfter): void
    {
        $nextSibling = $insertAfter->nextSibling;
        if ($nextSibling === null) {
            $parent->appendChild($node);

            return;
        }

        $parent->insertBefore($node, $nextSibling);
    }
}
