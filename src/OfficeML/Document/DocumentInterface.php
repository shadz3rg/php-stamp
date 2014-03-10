<?php

namespace OfficeML\Document;

interface DocumentInterface
{
    function extract($to, $overwrite);
    function getContentPath();
    function getTokenCollection(\DOMDocument $content);
} 