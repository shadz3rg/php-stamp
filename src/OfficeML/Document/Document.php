<?php

namespace OfficeML\Document;

use OfficeML\Exception\InvalidArgumentException;

abstract class Document implements DocumentInterface
{
    const XPATH_PARAGRAPH  = 0;
    const XPATH_RUN  = 1;
    const XPATH_PROPERTY  = 2;
    const XPATH_TEXT  = 3;

    public $documentName;
    public $documentPath;
    public $contentPath;

    /**
     * Constructor.
     *
     * @param string $documentPath
     * @throws InvalidArgumentException
     */
    public function __construct($documentPath)
    {
        if (file_exists($documentPath) === false) {
            throw new InvalidArgumentException('File not found.');
        }

        $this->documentPath = $documentPath;
        $this->documentName = pathinfo($this->documentPath, PATHINFO_BASENAME);
    }

    /**
     * Extract content file from document.
     *
     * @param string $to
     * @param bool $overwrite
     * @return string
     * @throws InvalidArgumentException
     */
    public function extract($to, $overwrite)
    {
        $filePath = $to . $this->documentName . '/' . $this->getContentPath();

        if (!file_exists($filePath) || $overwrite === true) {
            $zip = new \ZipArchive();

            $code = $zip->open($this->documentPath);
            if ($code !== true) {
                throw new InvalidArgumentException(
                    'Can`t open archive "' . $this->documentPath . '", code "' . $code . '" returned.'
                );
            }

            if ($zip->extractTo($to . $this->documentName, $this->getContentPath()) === false) {
                throw new InvalidArgumentException('Destination not reachable.');
            }
        }

        return $filePath;
    }

    abstract public function getContentPath();
    abstract public function getTokenCollection(\DOMDocument $content, array $brackets);
    abstract public function getNodeStructure();
    abstract public function getTextQuery();
}