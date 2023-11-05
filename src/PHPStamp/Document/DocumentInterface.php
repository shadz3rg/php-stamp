<?php

namespace PHPStamp\Document;

use PHPStamp\Exception\InvalidArgumentException;
use PHPStamp\Extension\ExtensionInterface;
use PHPStamp\Processor\Tag;

interface DocumentInterface
{
    /**
     * Constructor.
     *
     * @param string $documentPath
     *
     * @throws InvalidArgumentException
     */
    public function __construct($documentPath);

    /**
     * Extract content file from document.
     *
     * @param string $to
     * @param bool   $overwrite
     *
     * @return string
     *
     * @throws InvalidArgumentException
     */
    public function extract($to, $overwrite);

    /**
     * Get document file hash.
     *
     * @return string
     */
    public function getDocumentHash();

    /**
     * Get document filename with extension.
     *
     * @return string
     */
    public function getDocumentName();

    /**
     * Get path to document file.
     *
     * @return string
     */
    public function getDocumentPath();

    /**
     * Cleanup content xml file.
     *
     * @return void
     */
    public function cleanup(\DOMDocument $template);

    /**
     * Get path to content file inside document archive.
     *
     * @return string
     */
    public static function getContentPath();

    /**
     * Get xpath to parent node what can contain text node with tag.
     *
     * @return string
     */
    public function getNodePath();

    /**
     * Get node name of given type.
     *
     * @return string
     */
    public function getNodeName(int $type, bool $global = false);

    /**
     * Get expression with given id/.
     *
     * @return ExtensionInterface
     */
    public function getExpression(string $id, Tag $tag);
}
