<?php
namespace OfficeML;

class XMLHelper
{
    /**
     * @param $node1 \DOMNode|null
     * @param $node2 \DOMNode|null
     * @return bool
     * @link http://www.w3.org/TR/2003/WD-DOM-Level-3-Core-20030226/DOM3-Core.html#core-Node3-isEqualNode
     * @link https://github.com/WebKit/webkit/blob/master/Source/WebCore/dom/Node.cpp#L1060
     */
    public function deepEqual($node1, $node2)
    {
        if (($node1 === null && $node2 !== null) || ($node1 !== null && $node2 === null)) {
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
            if ($node2Child === null || $this->deepEqual($node1Child, $node2Child) === false) {
                return false;
            }

            $node1Child = $node1Child->nextSibling;
            $node2Child = $node2Child->nextSibling;
        }

        if ($node2Child !== null) { // if $node2 child count > $node1 child count
            return false;
        }

        // 4 Compare document types
        $node1DocumentType = $node1->ownerDocument->doctype;
        $node2DocumentType = $node2->ownerDocument->doctype;

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

    private function compareAttributes(\DOMNode $node1, \DOMNode $node2)
    {
        if ($node1->hasAttributes() === false && $node2->hasAttributes() === false) {
            return true;
        }

        if ($node1->hasAttributes() !== $node2->hasAttributes()) {
            return false;
        }

        if ($node1->attributes->length !== $node2->attributes->length) {
            return false;
        }

        /** @var $attribute \DOMNode */
        foreach ($node1->attributes as $attribute) {
            // TODO namespace problem, localName as fast fix
            $compareAgainst = $node2->attributes->getNamedItem($attribute->localName);

            if ($compareAgainst === null || $attribute->nodeValue !== $compareAgainst->nodeValue) {
                return false;
            }
        }
    }
} 