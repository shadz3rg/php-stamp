<?php
namespace OfficeML\Cache;

use OfficeML\Exception\TokenException;

class DocxNodeCollection implements NodeCollectionInterface
{
    /**
     * @var int
     */
    const LEFT_BRACKET = 0;
    /**
     * @var int
     */
    const RIGHT_BRACKET = 1;
    /**
     * @var \DOMXPath
     */
    public $xpath;
    /**
     * @var array
     */
    public $brackets;

    public function __construct(\DOMXPath $xpath, array $brackets)
    {
        $this->xpath = $xpath;
        $this->brackets = $brackets;
    }
    public function getParagraphNodes()
    {
        $query = sprintf(
            '//w:p[contains(., "%s")][contains(., "%s")]',
            $this->brackets[self::LEFT_BRACKET],
            $this->brackets[self::RIGHT_BRACKET]
        );

        $nodes = $this->xpath->query($query);
        if ($nodes->length === 0) {
            throw new TokenException('Tokens not found.');
        }

        return $nodes;
    }
    public function getPartialNodes(\DOMNode $parentNode)
    {
        $nodes = $this->xpath->query('.//w:r', $parentNode);
        if ($nodes->length === 0) {
            throw new TokenException('Tokens not found.');
        }

        return $nodes;
    }
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