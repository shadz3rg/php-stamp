<?php

namespace OfficeML\Document;

use OfficeML\Processor\TokenMapper;

class WordDocument extends Document
{
    public function getContentPath()
    {
        return 'word/document.xml';
    }

    public function getTokenCollection(\DOMDocument $content, array $brackets)
    {
        // TODO Brackets
        $mapper = new TokenMapper($content, $brackets);
        return $mapper->parseForTokens('//w:p');
    }

    public function getNodeStructure()
    {
        return array(
            '//w:p', // strict path not needed
            'w:r',
            'w:rPr',
            'w:t'
        );
    }
} 