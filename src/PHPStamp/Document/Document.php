<?php

namespace PHPStamp\Document;

use PHPStamp\Exception\HashException;
use PHPStamp\Exception\InvalidArgumentException;
use PHPStamp\Extension\ExtensionInterface;
use PHPStamp\Processor\Tag;

abstract class Document implements DocumentInterface
{
    public const XPATH_PARAGRAPH = 0;
    public const XPATH_RUN = 1;
    public const XPATH_RUN_PROPERTY = 2;
    public const XPATH_TEXT = 3;

    /**
     * Original document filename.
     *
     * @var string
     */
    private $documentName;

    /**
     * Path to original document.
     *
     * @var string
     */
    private $documentPath;

    /**
     * Creates a new Document.
     *
     * @param string $documentPath
     *
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
     * Extract main content file.
     *
     * @param string $to        path to extract content file
     * @param bool   $overwrite overwrite content file
     *
     * @return string full path to extracted document file
     *
     * @throws InvalidArgumentException
     */
    public function extract($to, $overwrite)
    {
        $filePath = $to.$this->getDocumentName().'/'.$this->getContentPath();

        if (!file_exists($filePath) || $overwrite === true) {
            $zip = new \ZipArchive();

            $code = $zip->open($this->getDocumentPath());
            if ($code !== true) {
                throw new InvalidArgumentException('Can`t open archive "'.$this->documentPath.'", code "'.$code.'" returned.');
            }

            if ($zip->extractTo($to.$this->documentName, $this->getContentPath()) === false) {
                throw new InvalidArgumentException('Destination not reachable.');
            }
        }

        return $filePath;
    }

    /**
     * Get MD5 hash to detect original document update.
     */
    public function getDocumentHash()
    {
        $hash = md5_file($this->documentPath);
        if ($hash === false) {
            throw new HashException();
        }

        return $hash;
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
    abstract public function cleanup(\DOMDocument $template);

    /**
     * @inherit
     */
    abstract public static function getContentPath();

    /**
     * @inherit
     */
    abstract public function getNodePath();

    /**
     * @inherit
     *
     * @param int  $type   XPATH_* constant
     * @param bool $global append global xpath //
     */
    abstract public function getNodeName($type, $global = false);

    /**
     * @inherit
     *
     * @param string $id  id as entered in placeholder
     * @param Tag    $tag container tag
     */
    abstract public function getExpression(string $id, Tag $tag): ExtensionInterface;
}
