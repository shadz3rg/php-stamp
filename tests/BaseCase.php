<?php

namespace PHPStamp\Tests;

use PHPStamp\Document\Document;
use PHPStamp\Document\DocumentInterface;

class BaseCase extends \PHPUnit\Framework\TestCase
{
    public static function makeMockDocument(string $content, string $instance, string $filename): DocumentInterface
    {
        $dir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'docx';
        if (file_exists($dir) === false) {
            mkdir($dir);
        }

        return self::makeMockDocumentAt($content, $instance, $dir.DIRECTORY_SEPARATOR.$filename);
    }

    public static function makeMockDocumentAt(string $content, string $instance, string $path): DocumentInterface
    {
        $zip = new \ZipArchive();

        if ($zip->open($path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \Exception('Cant open archive '.$path);
        }

        $zip->addFromString($instance::getContentPath(), $content);
        $zip->close();

        /** @var Document $doc */
        $doc = new $instance($path);

        return $doc;
    }
}
