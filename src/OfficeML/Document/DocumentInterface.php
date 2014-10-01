<?php

namespace OfficeML\Document;

interface DocumentInterface
{
    public function extract($to, $overwrite);
    public function getContentPath();
    public function getNodePath();
    public function getNodeQuery($type, $global = false);
} 