<?php

namespace OfficeML\Processor\NodeCollection;

use OfficeML\Exception\TokenException;

class DocxNodeCollection implements NodeCollectionInterface
{
    const LEFT = 0;
    const RIGHT = 1;

    private $xpath;
    private $brackets;

    /**
     * @param \DOMXPath $xpath
     * @param array $brackets
     */
    public function __construct(\DOMXPath $xpath, array $brackets)
    {
        $this->xpath = $xpath;
        $this->brackets = $brackets;
    }

    /**
     * @return \DOMNodeList
     * @throws \OfficeML\Exception\TokenException
     */
    public function getParagraphNodes()
    {
        $query = sprintf(
            '//w:p[contains(., "%s")][contains(., "%s")]',
            $this->brackets[self::LEFT],
            $this->brackets[self::RIGHT]
        );

        $nodes = $this->xpath->query($query);
        if ($nodes->length === 0) {
            throw new TokenException('Tokens not found.');
        }

        return $nodes;
    }

    /**
     * @param \DOMNode $parentNode
     * @return \DOMNodeList
     * @throws \OfficeML\Exception\TokenException
     */
    public function getPartialNodes(\DOMNode $parentNode)
    {
        $nodes = $this->xpath->query('.//w:r', $parentNode);
        if ($nodes->length === 0) {
            throw new TokenException('Tokens not found.');
        }

        return $nodes;
    }

    /**
     * @param \DOMNode $partialNode
     * @return \DOMNode
     * @throws \OfficeML\Exception\TokenException
     */
    public function getTextNode(\DOMNode $partialNode)
    {
        $nodes = $this->xpath->query('w:t', $partialNode);
        if ($nodes->length !== 1) {
            throw new TokenException('Unexpected multiple w:t elements.');
        }
        $node = $nodes->item(0);

        return $node;
    }
} 