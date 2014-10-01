<?php
namespace OfficeML;

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
        $paragraphs = $this->getParagraphNodeList();

        /** @var $paragraph \DOMNode */
        foreach ($paragraphs as $paragraph) {
            $clonedParagraph = $paragraph->cloneNode(true); // fixed missing paragraph props element

            $runNodeList = $this->getRunNodeList($clonedParagraph);

            $runIndex = 0;
            $currentRun = $runNodeList->item($runIndex);
            $nextRun = $runNodeList->item(++$runIndex);

            while ($currentRun) {
                $isEqual = false;

                if ($nextRun !== null) {
                    $isEqual = $this->deepEqual(
                        $this->getPropertyNode($currentRun),
                        $this->getPropertyNode($nextRun)
                    );
                }

                if ($isEqual === true) {
                    $this->getValueNode($currentRun)->nodeValue .= $this->getValueNode($nextRun)->nodeValue;
                    $clonedParagraph->removeChild($nextRun);
                } else {
                    //$clonedParagraph->appendChild($currentRun);
                    $currentRun = $nextRun;
                }

                if ($nextRun !== null) {
                    $nextRun = $runNodeList->item(++$runIndex);
                }
            }

            $paragraph->parentNode->replaceChild($clonedParagraph, $paragraph);
        }
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

    public function hardcoreCleanup() // TODO Move into document specific class
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