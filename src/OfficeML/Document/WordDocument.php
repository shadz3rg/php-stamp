<?php

namespace OfficeML\Document;

use OfficeML\Processor\TagMapper;

class WordDocument extends Document
{
    private $structure = array(
                '//w:p', // strict path not needed
                'w:r',
                'w:rPr',
                'w:t'
            );


    public function getContentPath()
    {
        return 'word/document.xml';
    }

    public function getTokenCollection(\DOMDocument $content, array $brackets)
    {
        // TODO Brackets
        $mapper = new TagMapper($content, $brackets);
        return $mapper->parseForTokens('//w:p');
    }

    public function getNodeStructure()
    {
        return $this->structure;
    }

    public function getTextQuery()
    {
        return '//w:p/w:r/w:t'; // FIXME
    }
} 