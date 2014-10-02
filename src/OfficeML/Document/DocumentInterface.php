<?php

namespace OfficeML\Document;

interface DocumentInterface
{
    public function extract($to, $overwrite);
    public function getContentPath();
    public function getDocumentName();
    public function getDocumentPath();

    public function getNodePath(); // TODO Remove?
    public function getNodeQuery($type, $global = false);

    public function cleanup(\DOMDocument $template);
} 