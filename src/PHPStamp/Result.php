<?php

namespace PHPStamp;

use PHPStamp\Document\Document;
use PHPStamp\Document\DocumentInterface;

class Result
{
    /**
     * @var \DOMDocument
     */
    private $output;
    /**
     * @var DocumentInterface
     */
    private $document;
    /**
     * @var \DOMDocument[]
     */
    private $footerList;
    /**
     * @var \DOMDocument[]
     */
    private $headerList;

    public function __construct(\DOMDocument $mainDocument,
        $headerDocumentList, $footerDocumentList, DocumentInterface $document)
    {
        $this->output = $mainDocument;
        $this->headerList = $headerDocumentList;
        $this->footerList = $footerDocumentList;
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
            if (ob_get_contents()) ob_clean();
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
            foreach($this->headerList as $i => $header) {
                $zip->addFromString($this->document->getHeaderPath($i), $header->saveXML());
            }
            foreach($this->footerList as $i => $footer) {
                $zip->addFromString($this->document->getFooterPath($i), $footer->saveXML());
            }
            $zip->close();
            return $tempArchive;
        }

        return false;
    }
} 
