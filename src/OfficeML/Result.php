<?php

namespace OfficeML;

class Result
{
    private $output;
    private $document;

    public function __construct(\DOMDocument $output, Document $document)
    {
        $this->output = $output;
        $this->document = $document;
    }

    public function getDOMDocument()
    {
        return $this->output;
    }

    public function download()
    {
        $tempArchive = tempnam(sys_get_temp_dir(), 'doc');

        if (copy($this->document->documentPath, $tempArchive) === true) {
            $zip = new \ZipArchive();
            $zip->open($tempArchive);
            $zip->addFromString($this->document->getContentPath(), $this->output->saveXML());
            $zip->close();

            header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
            header('Content-Disposition: attachment;filename="' . $this->document->documentName . '.docx"');

            // Send file - required ob_clean() & exit;
            ob_clean();
            readfile($tempArchive);
            unlink($tempArchive);
            exit;
        }
    }
} 