<?php

namespace OfficeML\Document;

use OfficeML\Processor\TagMapper;

/**
 * @link http://msdn.microsoft.com/ru-ru/library/office/gg278327(v=office.15).aspx
 */
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

    public function getNodeStructure()
    {
        return $this->structure;
    }

    public function getNodePath()
    {
        return '//w:p/w:r/w:t'; // FIXME
    }
} 