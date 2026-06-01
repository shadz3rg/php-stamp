<?php

namespace PHPStamp;

use PHPStamp\Document\DocumentInterface;
use PHPStamp\Exception\TempException;
use PHPStamp\Exception\XmlException;

class Result
{
    /**
     * XML results of processed XSL templates.
     *
     * @var array<string,\DOMDocument>
     */
    private array $outputs;

    /**
     * Document to render.
     *
     * @var DocumentInterface document to render
     */
    private $document;

    /**
     * Create a new render Result.
     *
     * @param \DOMDocument|array<string,\DOMDocument> $output   XML result of processed XSL template
     * @param DocumentInterface                       $document document to render
     */
    public function __construct($output, DocumentInterface $document)
    {
        $this->document = $document;

        if ($output instanceof \DOMDocument) {
            $output = [$document->getContentPath() => $output];
        }

        $this->outputs = $output;
    }

    /**
     * Get XML result of processed XSL template.
     *
     * @return \DOMDocument
     */
    public function getContent()
    {
        return $this->outputs[$this->document->getContentPath()];
    }

    /**
     * Get all XML results of processed XSL templates by archive path.
     *
     * @return array<string,\DOMDocument>
     */
    public function getContents()
    {
        return $this->outputs;
    }

    /**
     * Simple HTTP download method.
     *
     * @deprecated use your framework to serve files correctly
     * @see https://symfony.com/doc/current/components/http_foundation.html#serving-files
     *
     * @param null $fileName
     */
    public function download($fileName = null): void
    {
        if ($fileName === null) {
            $fileName = $this->document->getDocumentName();
        }

        $tempFile = $this->buildFile();
        if ($tempFile !== false) {
            header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
            header('Content-Disposition: attachment;filename="'.$fileName.'"');

            // Send file - required ob_clean() & exit;
            if (ob_get_contents()) {
                ob_clean();
            }
            readfile($tempFile);
            unlink($tempFile);
            exit; /* @phpstan-ignore-line */
        }
    }

    /**
     * Merge XML result with original document into temp file.
     *
     * @return false|string path to built file or false on some error
     *
     * @throws TempException
     * @throws XmlException
     */
    public function buildFile()
    {
        $tempDir = sys_get_temp_dir();
        $tempArchive = tempnam($tempDir, 'doc');
        if ($tempArchive === false) {
            throw new TempException(sprintf('Cannot acquire temp file at %s', $tempDir));
        }

        if (copy($this->document->getDocumentPath(), $tempArchive) === true) {
            $zip = new \ZipArchive();
            $code = $zip->open($tempArchive);
            if ($code !== true) {
                unlink($tempArchive);

                throw new TempException('Cannot open temp archive, code "'.$code.'" returned.');
            }

            foreach ($this->outputs as $contentPath => $output) {
                $content = $output->saveXML();
                if ($content === false) {
                    $zip->close();
                    unlink($tempArchive);

                    throw new XmlException('Print XML error');
                }

                if ($zip->addFromString($contentPath, $content) !== true) {
                    $zip->close();
                    unlink($tempArchive);

                    throw new TempException('Cannot write document content to temp archive.');
                }
            }

            $zip->close();

            return $tempArchive;
        }

        return false;
    }

    /**
     * Build file and save to filesystem.
     *
     * @param string      $destinationPath destination dir with no trailing slash
     * @param string|null $fileName        file name, use original document name if no value present
     *
     * @return bool
     */
    public function save($destinationPath, $fileName = null)
    {
        if ($fileName === null) {
            $fileName = $this->document->getDocumentName();
        }

        $tempFile = $this->buildFile();
        if ($tempFile !== false) {
            $result = copy($tempFile, $destinationPath.'/'.$fileName);
            unlink($tempFile);

            return $result;
        }

        return false;
    }

    /**
     * Build file and output to buffer.
     * Useful for framework integration, such as Symfony Response object.
     *
     * @return string|false file content or false on error
     */
    public function output()
    {
        $tempFile = $this->buildFile();
        if ($tempFile !== false) {
            $output = file_get_contents($tempFile);
            unlink($tempFile);

            return $output;
        }

        return false;
    }
}
