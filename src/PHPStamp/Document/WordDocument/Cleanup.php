<?php

namespace PHPStamp\Document\WordDocument;

use PHPStamp\XMLHelper;

class Cleanup extends XMLHelper
{
    /**
     * @var \DOMDocument
     */
    private $document;
    private $xpath;

    private $paragraphQuery;
    private $runQuery;
    private $runPropertyQuery;
    private $textQuery;

    public function __construct(\DOMDocument $document, $paragraphQuery, $runQuery, $propertyQuery, $textQuery)
    {
        $this->document = $document;
        $this->xpath = new \DOMXPath($document);

        $this->paragraphQuery = $paragraphQuery;
        $this->runQuery = $runQuery;
        $this->runPropertyQuery = $propertyQuery;
        $this->textQuery = $textQuery;
    }

    public function cleanup()
    {
        $paragraphNodeList = $this->getParagraphNodeList();

        /** @var $paragraphNode \DOMNode */
        foreach ($paragraphNodeList as $paragraphNode) {
            $clonedParagraphNode = $paragraphNode->cloneNode(true); // fixed missing paragraph props element
            $runNodeList = $this->getRunNodeList($clonedParagraphNode);

            $runIndex = 0;
            $currentRunNode = $runNodeList->item($runIndex);

            $runIndex += 1;
            $nextRunNode = $runNodeList->item($runIndex);

            while ($currentRunNode) {
                if ($nextRunNode !== null) {
                    $isEqual = $this->deepEqual(
                        $this->getPropertyNode($currentRunNode),
                        $this->getPropertyNode($nextRunNode)
                    );

                    if ($isEqual === true) {
                        $appendTextNode = $this->document->createTextNode(
                            $this->getValueNode($nextRunNode)->textContent
                        );
                        $this->getValueNode($currentRunNode)->appendChild($appendTextNode);

                        $clonedParagraphNode->removeChild($nextRunNode);
                    } else {
                        $currentRunNode = $nextRunNode;
                    }

                    // even if we remove element from document node list still contains it, so jump on next
                    $runIndex += 1;
                    $nextRunNode = $runNodeList->item($runIndex);

                } else {
                    $currentRunNode = $nextRunNode;
                }
            }
            $paragraphNode->parentNode->replaceChild($clonedParagraphNode, $paragraphNode);
        }

        // merge appended text nodes
        $this->document->normalizeDocument();
    }

    private function getParagraphNodeList()
    {
        return $this->xpath->query($this->paragraphQuery);
    }

    private function getRunNodeList(\DOMNode $paragraphNode)
    {
        return $this->xpath->query($this->runQuery, $paragraphNode);
    }

    private function getPropertyNode(\DOMNode $runNode)
    {
        $nodeList = $this->xpath->query($this->runPropertyQuery, $runNode);
        return $nodeList->item(0);
    }

    private function getValueNode(\DOMNode $runNode)
    {
        $nodeList = $this->xpath->query($this->textQuery, $runNode);
        return $nodeList->item(0);
    }

    public function hardcoreCleanup()
    {
        // reset locale
        $nodeList = $this->xpath->query('//w:lang');
        /** @var $langNode \DOMNode */
        foreach ($nodeList as $langNode) {
            $langNode->parentNode->removeChild($langNode);
        }

        // cleanup empty rPr
        $nodeList = $this->xpath->query('//' . $this->runPropertyQuery . '[not(node())]');
        /** @var $langNode \DOMNode */
        foreach ($nodeList as $node) {
            $node->parentNode->removeChild($node);
        }
    }
}