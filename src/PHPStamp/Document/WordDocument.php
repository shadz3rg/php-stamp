<?php

namespace PHPStamp\Document;

use PHPStamp\Document\WordDocument\Cleanup;
use PHPStamp\Exception\InvalidArgumentException;
use PHPStamp\Extension\Extension;
use PHPStamp\Processor\Tag;

/**
 * @see http://msdn.microsoft.com/ru-ru/library/office/gg278327(v=office.15).aspx
 */
class WordDocument extends Document
{
    private $structure = ['w:p', 'w:r', 'w:rPr', 'w:t'];

    /**
     * Path to main content file inside document ZIP archive.
     */
    public function getContentPath()
    {
        return 'word/document.xml';
    }

    /**
     * Get node name by XPATH_* constant type.
     *
     * @param int  $type   XPATH_* constant
     * @param bool $global append global xpath //
     *
     * @return string
     *
     * @throws InvalidArgumentException
     */
    public function getNodeName($type, $global = false)
    {
        if (isset($this->structure[$type]) === false) {
            throw new InvalidArgumentException('Element with this index not defined in structure');
        }

        $return = [];
        if ($global === true) {
            $return[] = '//';
        }
        $return[] = $this->structure[$type];

        return implode($return);
    }

    /**
     * XPath to text node.
     */
    public function getNodePath()
    {
        return '//w:p/w:r/w:t';
    }

    /**
     * Cleanup Word Document from WYSIWYG mess.
     *
     * @throws InvalidArgumentException
     */
    public function cleanup(\DOMDocument $template)
    {
        // fix node breaks
        $cleaner = new Cleanup(
            $template,
            $this->getNodeName(Document::XPATH_PARAGRAPH, true),
            $this->getNodeName(Document::XPATH_RUN),
            $this->getNodeName(Document::XPATH_RUN_PROPERTY),
            $this->getNodeName(Document::XPATH_TEXT)
        );

        $cleaner->hardcoreCleanup();
        $cleaner->cleanup();
    }

    /**
     * Get instance of associated placeholder function.
     *
     * @param string $id  id as entered in placeholder
     * @param Tag    $tag container tag
     *
     * @return Extension
     *
     * @throws InvalidArgumentException
     */
    public function getExpression($id, Tag $tag)
    {
        $available = [
            'cell' => 'PHPStamp\\Document\\WordDocument\\Extension\\Cell',
            'listitem' => 'PHPStamp\\Document\\WordDocument\\Extension\\ListItem',
        ];

        if (isset($available[$id]) === false) {
            throw new InvalidArgumentException('Class by id "'.$id.'" not found.');
        }

        $className = $available[$id];

        return new $className($tag);
    }
}
