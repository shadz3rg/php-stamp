<?php

namespace PHPStamp\Document;

use PHPStamp\Document\WordDocument\Cleanup;
use PHPStamp\Exception\InvalidArgumentException;
use PHPStamp\Processor\Tag;

/**
 * @link http://msdn.microsoft.com/ru-ru/library/office/gg278327(v=office.15).aspx
 */
class WordDocument extends Document
{
    private $structure = array('w:p', 'w:r', 'w:rPr', 'w:t');

    /**
     * @inherit
     */
    public function getContentPath()
    {
        return 'word/document.xml';
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
        return '//w:p/w:r/w:t';
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
        $available = array(
			'cell' => 'PHPStamp\\Document\\WordDocument\\Extension\\Cell',
			'listitem' => 'PHPStamp\\Document\\WordDocument\\Extension\\ListItem',
		);
		
		if (isset($available[$id]) === false) {
			throw new InvalidArgumentException('Class by id "' . $id . '" not found.');
		}
		
        $className = $available[$id];
        return new $className($tag);
    }
}
