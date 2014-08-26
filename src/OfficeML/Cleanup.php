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
    private $propertyQuery;
    private $textQuery;

    public function __construct(\DOMDocument $document, $paragraphQuery, $runQuery, $propertyQuery, $textQuery)
    {
        $this->document = $document;
        $this->xpath = new \DOMXPath($document);

        $this->paragraphQuery = $paragraphQuery;
        $this->runQuery = $runQuery;
        $this->propertyQuery = $propertyQuery;
        $this->textQuery = $textQuery;
    }

    public function cleanup()
    {
        $paragraphs = $this->getParagraphNodeList();

        /** @var $paragraph \DOMNode */
        foreach ($paragraphs as $paragraph) {
            $clonedParagraph = $paragraph->cloneNode();

            $runs = $this->getRunNodeList($paragraph);

            $runIndex = 0;
            $currentRun = $runs->item($runIndex);
            $nextRun = $runs->item(++$runIndex);

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
                } else {
                    $clonedParagraph->appendChild($currentRun);
                    $currentRun = $nextRun;
                }

                if ($nextRun !== null) {
                    $nextRun = $runs->item(++$runIndex);
                }
            }

            $paragraph->parentNode->replaceChild($clonedParagraph, $paragraph);
        }

        return $this->document;
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
        $nodeList = $this->xpath->query($this->propertyQuery, $runNode);
        return $nodeList->item(0);
    }

    private function getValueNode(\DOMNode $runNode)
    {
        $nodeList = $this->xpath->query($this->textQuery, $runNode);
        return $nodeList->item(0);
    }
}