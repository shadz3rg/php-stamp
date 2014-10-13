<?php

namespace PHPStamp\Document;

use PHPStamp\Exception\InvalidArgumentException;
use PHPStamp\Processor\Tag;

abstract class Document implements DocumentInterface
{
    const XPATH_PARAGRAPH  = 0;
    const XPATH_RUN  = 1;
    const XPATH_RUN_PROPERTY  = 2;
    const XPATH_TEXT  = 3;

    /**
     * @var string
     */
    private $documentName;
    /**
     * @var string
     */
    private $documentPath;

    /**
     * @inherit
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
     * @inherit
     */
    public function extract($to, $overwrite)
    {
        $filePath = $to . $this->getDocumentName() . '/' . $this->getContentPath();

        if (!file_exists($filePath) || $overwrite === true) {
            $zip = new \ZipArchive();

            $code = $zip->open($this->getDocumentPath());
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

    /**
     * @inherit
     */
    public function getDocumentName()
    {
        return $this->documentName;
    }

    /**
     * @inherit
     */
    public function getDocumentPath()
    {
        return $this->documentPath;
    }

    /**
     * @inherit
     */
    public abstract function cleanup(\DOMDocument $template);

    /**
     * @inherit
     */
    public abstract function getContentPath();

    /**
     * @inherit
     */
    public abstract function getNodePath();

    /**
     * @inherit
     */
    public abstract function getNodeName($type, $global = false);

   /**
    * @inherit
    */
    public abstract function getExpression($id, Tag $tag);
}