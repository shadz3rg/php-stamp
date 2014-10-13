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
     * @throws InvalidArgumentException
     */
    function __construct($documentPath);

    /**
     * Extract content file from document.
     *
     * @param string $to
     * @param bool $overwrite
     * @return string
     * @throws InvalidArgumentException
     */
    function extract($to, $overwrite);

    /**
     * Get document filename with extension.
     *
     * @return string
     */
    function getDocumentName();

    /**
     * Get path to document file.
     *
     * @return string
     */
    function getDocumentPath();

    /**
     * Cleanup content xml file.
     *
     * @param \DOMDocument $template
     * @return void
     */
    function cleanup(\DOMDocument $template);

    /**
     * Get path to content file inside document archive.
     *
     * @return string
     */
    function getContentPath();

    /**
     * Get xpath to parent node what can contain text node with tag.
     *
     * @return string
     */
    function getNodePath();

    /**
     * Get node name of given type.
     *
     * @param $type
     * @param bool $global
     * @return string
     */
    function getNodeName($type, $global = false);

    /**
     * Get expression with given id/
     *
     * @param $id
     * @param $tag
     * @return ExtensionInterface
     */
    function getExpression($id, Tag $tag);
} 