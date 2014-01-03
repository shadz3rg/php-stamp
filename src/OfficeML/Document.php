<?php

namespace OfficeML;

class Document
{
    const DOC_CONTENT = 'word/document.xml';

    public $documentName;
    public $documentPath;

    /**
     * Constructor.
     *
     * @param string $filePath
     * @throws Exception\ArgumentsException
     */
    public function __construct($filePath) {
        if (!file_exists($filePath)) {
            throw new Exception\ArgumentsException('File not found.');
        }

        $this->documentPath = $filePath;
        $this->documentName = pathinfo($this->documentPath, PATHINFO_BASENAME);
    }

    /**
     * Extract main content file from document.
     *
     * @param string $to
     * @param bool $overwrite
     * @return string
     * @throws Exception\ArgumentsException
     */
    public function extract($to, $overwrite = false) {
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
}