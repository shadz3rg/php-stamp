<?php

namespace OfficeML\Document;

use OfficeML\Exception\ArgumentsException;
use OfficeML\Processor\TokenMapper;

class Document
{
    public $documentName;
    public $documentPath;
    public $contentPath;

    /**
     * Constructor.
     *
     * @param string $filePath
     * @throws ArgumentsException
     */
    public function __construct($filePath)
    {
        if (!file_exists($filePath)) {
            throw new ArgumentsException('File not found.');
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
     * @throws ArgumentsException
     */
    public function extract($to, $overwrite)
    {
        $filePath = $to . $this->documentName . '/' . $this->getContentPath();

        if (!file_exists($filePath) || $overwrite === true) {
            $zip = new \ZipArchive();

            $code = $zip->open($this->documentPath);
            if ($code !== true) {
                throw new ArgumentsException(
                    'Can`t open archive "' . $this->documentPath . '", code "' . $code . '" returned.'
                );
            }

            if ($zip->extractTo($to . $this->documentName, $this->getContentPath()) === false) {
                throw new ArgumentsException('Destination not reachable.');
            }
        }

        return $filePath;
    }

    public function getContentPath()
    {
        return 'word/document.xml';
    }

    public function getTokenCollection(\DOMDocument $content)
    {
        // TODO Brackets
        $mapper = new TokenMapper($content, array('[[', ']]'));
        return $mapper->getTokens('//w:p');
    }
}