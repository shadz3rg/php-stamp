<?php

namespace OfficeML\Document;

interface DocumentInterface
{
    public function extract($to, $overwrite);
    public function getContentPath();
    public function getTokenCollection(\DOMDocument $content, array $brackets);
    public function getNodeStructure();
} 