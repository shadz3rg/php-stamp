<?php

namespace PHPStamp\Tests;

use PHPStamp\Document\Document;
use PHPStamp\Document\DocumentInterface;

class BaseCase extends \PHPUnit\Framework\TestCase
{
    public static function makeMockDocument(string $content, string $instance, string $filename): DocumentInterface
    {
        $zip = new \ZipArchive();

        $dir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'docx';
        if (file_exists($dir) === false) {
            mkdir($dir);
        }

        $filename = $dir.DIRECTORY_SEPARATOR.$filename;
        if ($zip->open($filename, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \Exception('Cant open archive '.$filename);
        }

        $zip->addFromString($instance::getContentPath(), $content);
        $zip->close();

        /** @var Document $doc */
        $doc = new $instance($filename);

        return $doc;
    }
}
