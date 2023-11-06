<?php

namespace PHPStamp;

use PHPStamp\Exception\ParsingException;
use PHPStamp\Exception\XmlException;

class XMLHelper
{
    /**
     * Check two given nodes for equality.
     *
     * @param \DOMNode|null $node1
     * @param \DOMNode|null $node2
     *
     * @throws XmlException
     *
     * @see https://github.com/WebKit/webkit/blob/master/Source/WebCore/dom/Node.cpp#L1081
     * @see http://www.w3.org/TR/2003/WD-DOM-Level-3-Core-20030226/DOM3-Core.html#core-Node3-isEqualNode
     */
    public function deepEqual($node1, $node2): bool
    {
        if ($node1 === null && $node2 === null) {
            return true;
        }

        if ($node1 === null || $node2 === null) {
            return false;
        }

        // 1 Compare properties
        if ($node1->nodeType !== $node2->nodeType) {
            return false;
        }
        if ($node1->nodeName !== $node2->nodeName) {
            return false;
        }
        if ($node1->localName !== $node2->localName) {
            return false;
        }
        if ($node1->namespaceURI !== $node2->namespaceURI) {
            return false;
        }
        if ($node1->prefix !== $node2->prefix) {
            return false;
        }
        if ($node1->nodeValue !== $node2->nodeValue) {
            return false;
        }

        // 2 Compare attributes
        if ($this->compareAttributes($node1, $node2) === false) {
            return false;
        }

        // 3 Compare child nodes recursively
        $node1Child = $node1->firstChild;
        $node2Child = $node2->firstChild;

        while ($node1Child) {
            if ($node2Child === null) { // if $node1 child count > $node2 child count
                return false;
            }

            if ($this->deepEqual($node1Child, $node2Child) === false) {
                return false;
            }

            $node1Child = $node1Child->nextSibling;
            $node2Child = $node2Child->nextSibling;
        }

        if ($node2Child !== null) { // if $node2 child count > $node1 child count
            return false;
        }

        // 4 Compare document types
        $node1Document = $node1->ownerDocument;
        if ($node1Document === null) {
            throw new XmlException('Detached node');
        }

        $node2Document = $node2->ownerDocument;
        if ($node2Document === null) {
            throw new XmlException('Detached node');
        }

        $node1DocumentType = $node1Document->doctype;
        $node2DocumentType = $node2Document->doctype;

        if ($node1DocumentType !== null && $node2DocumentType !== null) {
            if ($node1DocumentType->publicId !== $node2DocumentType->publicId) {
                return false;
            }
            if ($node1DocumentType->systemId !== $node2DocumentType->systemId) {
                return false;
            }
            if ($node1DocumentType->internalSubset !== $node2DocumentType->internalSubset) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check two given nodes for equal attributes.
     *
     * @throws XmlException
     */
    private function compareAttributes(\DOMNode $node1, \DOMNode $node2): bool
    {
        if ($node1->hasAttributes() === false && $node2->hasAttributes() === false) {
            return true;
        }

        if ($node1->hasAttributes() !== $node2->hasAttributes()) {
            return false;
        }

        if ($node1->attributes === null || $node2->attributes === null) {
            return false;
        }

        if ($node1->attributes->length !== $node2->attributes->length) {
            return false;
        }

        /** @var \DOMAttr $attribute */
        foreach ($node1->attributes as $attribute) {
            // namespace problem, localName as fix
            $localName = $attribute->localName;
            if ($localName === null) {
                throw new XmlException('Attr local-name is null (somehow)');
            }

            $compareAgainst = $node2->attributes->getNamedItem($localName);
            if ($compareAgainst === null || $attribute->nodeValue !== $compareAgainst->nodeValue) {
                return false;
            }
        }

        return true;
    }

    /**
     * Fetch node list from document.
     *
     * @return \DOMNodeList<\DOMNode>
     */
    public static function queryTemplate(\DOMDocument $document, string $xpathQuery)
    {
        $xpath = new \DOMXPath($document);
        $result = $xpath->query($xpathQuery);
        if ($result === false) {
            throw new ParsingException('Malformed query');
        }

        return $result;
    }

    /**
     * Formats DOMDocument for html output.
     *
     * @throws ParsingException
     */
    public static function prettyPrint(\DOMDocument $document): string
    {
        $document->preserveWhiteSpace = false;
        $document->formatOutput = true;

        $xmlString = $document->saveXML();
        if ($xmlString === false) {
            throw new ParsingException('Print XML error');
        }

        $document->preserveWhiteSpace = true;
        $document->formatOutput = false;

        return '<pre>'.htmlentities($xmlString).'</pre>';
    }

    /**
     * Find closest parent node.
     *
     * @throws Exception\ParsingException
     */
    public static function parentUntil(string $nodeName, \DOMNode $node): ?\DOMNode
    {
        $parent = $node->parentNode;
        if ($parent === null) {
            throw new ParsingException('Row not found.');
        }

        while ($parent->nodeName !== $nodeName) {
            $parent = $parent->parentNode;
            if ($parent === null) {
                throw new ParsingException('Row not found.');
            }
        }

        return $parent;
    }

    /**
     * Add associative array values into XML object recursively.
     *
     * @phpstan-param mixed $mixed
     *
     * @param string $itemName
     */
    public static function xmlEncode($mixed, \DOMNode $domElement, \DOMDocument $domDocument, $itemName = 'item'): void
    {
        if (is_array($mixed)) {
            foreach ($mixed as $index => $mixedElement) {
                $tagName = $index;
                if (is_int($index)) {
                    $tagName = $itemName;
                }

                $node = $domDocument->createElement($tagName);
                $domElement->appendChild($node);

                self::xmlEncode($mixedElement, $node, $domDocument, $itemName);
            }
        } elseif (is_scalar($mixed)) {
            $domElement->appendChild($domDocument->createTextNode((string) $mixed));
        }
    }
}
