<?php

namespace PHPStamp\Tests\Unit\PHPStamp\Document;

use PHPStamp\Document\WordDocument;
use PHPStamp\Tests\BaseCase;

class WordDocumentTest extends BaseCase
{
    public function testContentPath()
    {
        $file = __DIR__.'../../../resources/students.docx';

        $document = new WordDocument($file);

        $zip = new \ZipArchive();
        $zip->open($file);

        $content = $zip->getFromName($document->getContentPath());
        $this->assertNotFalse($content);

        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->loadXML($content);

        /** @var \DOMElement $root */
        $root = $doc->documentElement;
        $this->assertEquals($root->nodeValue, 'document');
    }
}
