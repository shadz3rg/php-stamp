<?php

namespace PHPStamp;

use PHPStamp\Document\DocumentInterface;

class Result
{
    /**
     * XML result of processed XSL template.
     *
     * @var \DOMDocument
     */
    private $output;

    /**
     * Document to render.
     *
     * @var DocumentInterface Document to render.
     */
    private $document;

    /**
     * Create a new render Result.
     *
     * @param \DOMDocument $output XML result of processed XSL template.
     * @param DocumentInterface $document Document to render.
     */
    public function __construct(\DOMDocument $output, DocumentInterface $document)
    {
        $this->output = $output;
        $this->document = $document;
    }

    /**
     * Get XML result of processed XSL template.
     *
     * @return \DOMDocument
     */
    public function getContent()
    {
        return $this->output;
    }

    /**
     * Simple HTTP download method.
     *
     * @param null $fileName
     */
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

    /**
     * Merge XML result with original document into temp file.
     *
     * @return false|string Path to built file.
     */
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
