<?php

namespace OfficeML\Document;

use OfficeML\Document\WordDocument\Cleanup;
use OfficeML\Exception\InvalidArgumentException;

/**
 * @link http://msdn.microsoft.com/ru-ru/library/office/gg278327(v=office.15).aspx
 */
class WordDocument extends Document
{
    private $structure = array('w:p', 'w:r', 'w:rPr', 'w:t');

    public function getContentPath()
    {
        return 'word/document.xml';
    }

    public function getNodeQuery($type, $global = false)
    {
        if (isset($this->structure[$type]) === false) {
            throw new InvalidArgumentException('Element with this index not defined in structure');
        }

        $return = array();
        if ($global === true) {
            $return[] = '//';
        }
        $return[] = $this->structure[$type];

        return implode($return);
    }

    public function getNodePath()
    {
        return '//w:p/w:r/w:t'; // FIXME
    }

    public function cleanup(\DOMDocument $template)
    {
        // fix node breaks
        $cleaner = new Cleanup(
            $template,
            $this->getNodeQuery(Document::XPATH_PARAGRAPH, true),
            $this->getNodeQuery(Document::XPATH_RUN),
            $this->getNodeQuery(Document::XPATH_RUN_PROPERTY),
            $this->getNodeQuery(Document::XPATH_TEXT)
        );

        $cleaner->hardcoreCleanup();
        $cleaner->cleanup();
    }
}