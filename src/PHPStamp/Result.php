<?php

namespace PHPStamp;

use PHPStamp\Document\Document;
use PHPStamp\Document\DocumentInterface;

class Result
{
    private $output;
    private $document;

    public function __construct(\DOMDocument $output, DocumentInterface $document)
    {
        $this->output = $output;
        $this->document = $document;
    }

    public function getContent()
    {
        return $this->output;
    }

    public function download($fileName = null)
    {
        if ($fileName === null) {
            $fileName = $this->document->getDocumentName();
        }

        $tempArchive = tempnam(sys_get_temp_dir(), 'doc');

        if (copy($this->document->getDocumentPath(), $tempArchive) === true) {
            $zip = new \ZipArchive();
            $zip->open($tempArchive);
            $zip->addFromString($this->document->getContentPath(), $this->output->saveXML());
            $zip->close();

            header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
            header('Content-Disposition: attachment;filename="' . $fileName . '"');

            // Send file - required ob_clean() & exit;
            ob_clean();
            readfile($tempArchive);
            unlink($tempArchive);
            exit;
        }
    }
    
    public function buildFile()
    {
        $tempArchive = tempnam(sys_get_temp_dir(), 'doc');
        if (copy($this->document->getDocumentPath(), $tempArchive) === true) {
            $zip = new \ZipArchive();
            $zip->open($tempArchive);
            $zip->addFromString($this->document->getContentPath(), $this->output->saveXML());
            $zip->close();
            return $tempArchive;
        }

        return false;
    }
} 
