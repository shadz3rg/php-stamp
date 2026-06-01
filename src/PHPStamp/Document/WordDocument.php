<?php

namespace PHPStamp\Document;

use PHPStamp\Document\WordDocument\Cleanup;
use PHPStamp\Document\WordDocument\LineBreaks;
use PHPStamp\Exception\InvalidArgumentException;
use PHPStamp\Extension\Extension;
use PHPStamp\Extension\ExtensionInterface;
use PHPStamp\Processor\Tag;

/**
 * @see http://msdn.microsoft.com/ru-ru/library/office/gg278327(v=office.15).aspx
 */
class WordDocument extends Document
{
    /**
     * @var array<string>
     */
    private array $structure = ['w:p', 'w:r', 'w:rPr', 'w:t'];

    /**
     * Path to main content file inside document ZIP archive.
     */
    public static function getContentPath()
    {
        return 'word/document.xml';
    }

    /**
     * Get renderable Word XML parts from document ZIP archive.
     *
     * @return array<string>
     */
    public function getContentPaths()
    {
        $paths = [self::getContentPath()];

        $zip = new \ZipArchive();
        $code = $zip->open($this->getDocumentPath());
        if ($code !== true) {
            throw new InvalidArgumentException('Can`t open archive "'.$this->getDocumentPath().'", code "'.$code.'" returned.');
        }

        for ($i = 0; $i < $zip->numFiles; ++$i) {
            $name = $zip->getNameIndex($i);
            if ($name === false) {
                continue;
            }

            if (preg_match('#^word/(?:header\d+|footer\d+|footnotes|endnotes|comments)\.xml$#', $name) === 1) {
                $paths[] = $name;
            }
        }

        $zip->close();

        return array_values(array_unique($paths));
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
     * Post-process Word Document content.
     */
    public function postProcess(\DOMDocument $content)
    {
        LineBreaks::replace($content);
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
    public function getExpression(string $id, Tag $tag): ExtensionInterface
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
