<?php
namespace OfficeML;

class Document {

    public $documentName;
    public $documentPath;

    function __construct($filePath) {
        if (!is_file($filePath)) {
            throw new Exception\ArgumentsException('File not found');
        }

        $this->documentPath = $filePath;
        $this->documentName = pathinfo($this->documentPath, PATHINFO_FILENAME);
    }

    public function extract($to, $contentPath) {
        $zip = new \ZipArchive();

        // Wow
        if ($zip->open($this->documentPath) !== true) {
            throw new Exception\ArgumentsException('Document not zip');
        }

        if ($zip->extractTo($to . $this->documentName, $contentPath) === false) {
            throw new Exception\ArgumentsException('Destination not reachable');
        }

        return $to . $this->documentName . '/' . $contentPath;
    }
}