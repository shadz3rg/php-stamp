<?php

namespace OfficeML\Document;

use OfficeML\Exception\ArgumentsException;
use OfficeML\Processor\TokenMapper;

class BasicDocument extends Document implements DocumentInterface
{
    public $documentName;
    public $documentPath;
    public $contentPath;

    public function __construct($filePath)
    {
        if (!file_exists($filePath)) {
            throw new ArgumentsException('File not found.');
        }

        $this->documentPath = $filePath;
        $this->documentName = pathinfo($this->documentPath, PATHINFO_BASENAME);
    }

    public function extract($to, $overwrite)
    {
        $filePath = $to . $this->documentName . '/' . $this->getContentPath();

        if (!file_exists($filePath) || $overwrite === true) {

            // just make cache dir and copy
            if (is_dir(dirname($filePath)) === false) {
                mkdir(dirname($filePath), 0777, true);
            }

            if (copy($this->documentPath, $filePath) === false) {
                throw new ArgumentsException('Cant copy file.');
            }
        }

        return $filePath;
    }

    public function getContentPath()
    {
        return $this->documentName;
    }

    public function getTokenCollection(\DOMDocument $content)
    {
        // TODO Brackets
        $mapper = new TokenMapper($content, array('[[', ']]'));
        return $mapper->getTokens('.//paragraph');
    }
}