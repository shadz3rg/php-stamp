<?php

namespace OfficeML\Document;

use OfficeML\Document\WriterDocument\Cleanup;
use OfficeML\Exception\InvalidArgumentException;
use OfficeML\Processor\Tag;

/**
 * @link http://msdn.microsoft.com/ru-ru/library/office/gg278327(v=office.15).aspx
 */
class WriterDocument extends Document
{
    private $structure = array('text:p', 'text:span', '', 'w:t');

    /**
     * @inherit
     */
    public function getContentPath()
    {
        return 'content.xml';
    }

    /**
     * @inherit
     */
    public function getNodeName($type, $global = false)
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

    /**
     * @inherit
     */
    public function getNodePath()
    {
        return '//text:p/text:span';
    }

    /**
     * @inherit
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
     * @inherit
     */
    public function getExpression($id, Tag $tag)
    {
        $className = 'OfficeML\\Document\\WordDocument\\Extension\\' . ucfirst($id);

        if (class_exists($className) === false) {
            throw new InvalidArgumentException('Class by id ' . $id . ' not found.');
        }

        return new $className($tag);
    }
}