<?php

namespace OfficeML;

use OfficeML\Exception\TokenException;

class Document
{
    const DOC_CONTENT = 'word/document.xml';

    public $documentName;
    public $documentPath;

    public $content;
    public $contentPath;

    private $xpath;

    /**
     * Constructor.
     *
     * @param string $filePath
     * @throws Exception\ArgumentsException
     */
    public function __construct($filePath)
    {
        if (!file_exists($filePath)) {
            throw new Exception\ArgumentsException('File not found.');
        }

        $this->documentPath = $filePath;
        $this->documentName = pathinfo($this->documentPath, PATHINFO_BASENAME);
    }

    /**
     * Extract content file from document.
     *
     * @param string $to
     * @param bool $overwrite
     * @return string
     * @throws Exception\ArgumentsException
     */
    public function extract($to, $overwrite)
    {
        $filePath = $to . $this->documentName . '/' . self::DOC_CONTENT;

        if (!file_exists($filePath) || $overwrite === true) {
            $zip = new \ZipArchive();

            $code = $zip->open($this->documentPath);
            if ($code !== true) {
                throw new Exception\ArgumentsException(
                    'Can`t open archive "' . $this->documentPath . '", code "' . $code . '" returned.'
                );
            }

            if ($zip->extractTo($to . $this->documentName, self::DOC_CONTENT) === false) {
                throw new Exception\ArgumentsException('Destination not reachable.');
            }
        }

        return $filePath;
    }


    /**
     * @param string $leftBracket
     * @param string $rightBracket
     * @return \DOMNodeList
     * @throws TokenException
     */
    public function getParagraphNodes($leftBracket, $rightBracket)
    {
        $query = sprintf(
            '//w:p[contains(., "%s")][contains(., "%s")]',
            $leftBracket,
            $rightBracket
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
     * @throws TokenException
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
     * @throws TokenException
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

    public function getContentPath()
    {
        return 'word/document.xml';
    }
}