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

    /**
     * Build a mock archive with arbitrary internal parts.
     * Array keys are paths inside the archive, values are the part contents.
     *
     * @param array<string,string> $parts
     */
    public static function makeMockDocumentWithParts(array $parts, string $instance, string $filename): DocumentInterface
    {
        $zip = new \ZipArchive();

        $dir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'docx';
        if (file_exists($dir) === false) {
            mkdir($dir);
        }

        $path = $dir.DIRECTORY_SEPARATOR.$filename;
        if ($zip->open($path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \Exception('Cant open archive '.$path);
        }

        foreach ($parts as $partPath => $content) {
            $zip->addFromString($partPath, $content);
        }

        $zip->close();

        /** @var Document $doc */
        $doc = new $instance($path);

        return $doc;
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
