<?php
namespace OfficeML;

class Helper
{
    /**
     * Add associative array values into XML object recursively.
     * @param $mixed
     * @param \DOMNode $domElement
     * @param \DOMDocument $domDocument
     * @param string $itemName
     */
    public static function xmlEncode($mixed, \DOMNode $domElement, \DOMDocument $domDocument, $itemName = 'item')
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
        } else {
            $domElement->appendChild($domDocument->createTextNode($mixed));
        }
    }
}