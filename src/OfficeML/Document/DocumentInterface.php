<?php

namespace OfficeML\Document;

use OfficeML\Extension\ExtensionInterface;
use OfficeML\Processor\Tag;

interface DocumentInterface
{
    public function extract($to, $overwrite);
    public function getContentPath();
    public function getDocumentName();
    public function getDocumentPath();

    public function getNodePath(); // TODO Remove?
    public function getNodeQuery($type, $global = false);

    public function cleanup(\DOMDocument $template);

    /**
     * @param $id
     * @param $tag
     * @return ExtensionInterface
     */
    public function getExpression($id, Tag $tag);
} 