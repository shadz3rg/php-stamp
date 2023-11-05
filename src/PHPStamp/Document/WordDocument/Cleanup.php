<?php

namespace PHPStamp\Document\WordDocument;

use PHPStamp\Exception\XmlException;
use PHPStamp\XMLHelper;

class Cleanup extends XMLHelper
{
    private \DOMDocument $document;
    private \DOMXPath $xpath;

    private string $paragraphQuery;
    private string $runQuery;
    private string $runPropertyQuery;
    private string $textQuery;

    public function __construct(\DOMDocument $document, string $paragraphQuery, string $runQuery, string $propertyQuery, string $textQuery)
    {
        $this->document = $document;
        $this->xpath = new \DOMXPath($document);

        $this->paragraphQuery = $paragraphQuery;
        $this->runQuery = $runQuery;
        $this->runPropertyQuery = $propertyQuery;
        $this->textQuery = $textQuery;
    }

    /**
     * @throws XmlException
     */
    public function cleanup(): void
    {
        $paragraphNodeList = $this->getParagraphNodeList();

        /** @var \DOMNode $paragraphNode */
        foreach ($paragraphNodeList as $paragraphNode) {
            $clonedParagraphNode = $paragraphNode->cloneNode(true); // fixed missing paragraph props element
            $runNodeList = $this->getRunNodeList($clonedParagraphNode);

            $runIndex = 0;
            $currentRunNode = $runNodeList->item($runIndex);

            ++$runIndex;
            $nextRunNode = $runNodeList->item($runIndex);

            while ($currentRunNode) {
                if ($nextRunNode !== null) {
                    $isEqual = $this->deepEqual(
                        $this->getPropertyNode($currentRunNode),
                        $this->getPropertyNode($nextRunNode)
                    );

                    if ($this->getValueNode($currentRunNode) === null || $this->getValueNode($nextRunNode) === null) {
                        $isEqual = false;
                    }

                    if ($isEqual === true) {
                        $nextValueNode = $this->getValueNode($nextRunNode);
                        $currentValueNode = $this->getValueNode($currentRunNode);

                        if ($nextValueNode !== null && $currentValueNode !== null) { // fixme libreoffice docx quick fix
                            $appendTextNode = $this->document->createTextNode($nextValueNode->textContent);
                            $currentValueNode->appendChild($appendTextNode);

                            if ($currentValueNode->hasAttribute('xml:space') === false
                                && $currentValueNode->textContent !== trim($currentValueNode->textContent)) {
                                $currentValueNode->setAttribute('xml:space', 'preserve');
                            }
                        }
                        $clonedParagraphNode->removeChild($nextRunNode);
                    } else {
                        $currentRunNode = $nextRunNode;
                    }

                    // even if we remove element from document node list still contains it, so jump on next
                    ++$runIndex;
                    $nextRunNode = $runNodeList->item($runIndex);
                } else {
                    $currentRunNode = $nextRunNode;
                }
            }

            $parentNode = $paragraphNode->parentNode;
            if ($parentNode === null) {
                throw new XmlException('Cant find container node');
            }

            $parentNode->replaceChild($clonedParagraphNode, $paragraphNode);
        }

        // merge appended text nodes
        $this->document->normalizeDocument();
    }

    /**
     * @return \DOMNodeList<\DOMNode>
     *
     * @throws XmlException
     */
    private function getParagraphNodeList()
    {
        $paragraphNodeList = $this->xpath->query($this->paragraphQuery);
        if ($paragraphNodeList === false) {
            throw new XmlException('Malformed query');
        }

        return $paragraphNodeList;
    }

    /**
     * @return \DOMNodeList<\DOMNode>
     *
     * @throws XmlException
     */
    private function getRunNodeList(\DOMNode $paragraphNode)
    {
        $runNodeList = $this->xpath->query($this->runQuery, $paragraphNode);
        if ($runNodeList === false) {
            throw new XmlException('Malformed query');
        }

        return $runNodeList;
    }

    /**
     * @throws XmlException
     */
    private function getPropertyNode(\DOMNode $runNode): ?\DOMElement
    {
        $nodeList = $this->xpath->query($this->runPropertyQuery, $runNode);
        if ($nodeList === false) {
            throw new XmlException('Malformed query');
        }

        /** @var \DOMElement|null $node */
        $node = $nodeList->item(0);

        return $node;
    }

    /**
     * @throws XmlException
     */
    private function getValueNode(\DOMNode $runNode): ?\DOMElement
    {
        $nodeList = $this->xpath->query($this->textQuery, $runNode);
        if ($nodeList === false) {
            throw new XmlException('Malformed query');
        }

        /** @var \DOMElement|null $node */
        $node = $nodeList->item(0);

        return $node;
    }

    /**
     * @throws XmlException
     */
    public function hardcoreCleanup(): void
    {
        // reset locale
        $nodeList = $this->xpath->query('//w:lang');
        if ($nodeList === false) {
            throw new XmlException('Malformed query');
        }

        /** @var \DOMNode $langNode */
        foreach ($nodeList as $langNode) {
            if ($langNode->parentNode === null) {
                continue;
            }
            $langNode->parentNode->removeChild($langNode);
        }

        // cleanup empty rPr
        $nodeList = $this->xpath->query('//'.$this->runPropertyQuery.'[not(node())]');
        if ($nodeList === false) {
            throw new XmlException('Malformed query');
        }

        /* @var \DOMNode $langNode */
        foreach ($nodeList as $node) {
            if ($node->parentNode === null) {
                continue;
            }
            $node->parentNode->removeChild($node);
        }
    }
}
