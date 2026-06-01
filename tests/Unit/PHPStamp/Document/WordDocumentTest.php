<?php

namespace PHPStamp\Tests\Unit\PHPStamp\Document;

use PHPStamp\Document\WordDocument;
use PHPStamp\Tests\BaseCase;

class WordDocumentTest extends BaseCase
{
    public function testContentPath(): void
    {
        $file = __DIR__.'/../../../resources/students_ms.docx';

        $document = new WordDocument($file);

        $zip = new \ZipArchive();
        $zip->open($file);

        $content = $zip->getFromName($document->getContentPath());
        $this->assertNotFalse($content);

        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->loadXML($content);

        /** @var \DOMElement $root */
        $root = $doc->documentElement;
        $this->assertEquals('w:document', $root->nodeName);
    }

    public function testIncludedParts(): void
    {
        $document = $this->makeMockDocumentWithParts(
            [
                WordDocument::getContentPath() => '<w:document xmlns:w="https://schemas.openxmlformats.org/wordprocessingml/2006/main"/>',
                'word/header1.xml' => '<w:hdr xmlns:w="https://schemas.openxmlformats.org/wordprocessingml/2006/main"/>',
                'word/footer1.xml' => '<w:ftr xmlns:w="https://schemas.openxmlformats.org/wordprocessingml/2006/main"/>',
                'word/settings.xml' => '<w:settings xmlns:w="https://schemas.openxmlformats.org/wordprocessingml/2006/main"/>',
            ],
            WordDocument::class,
            'content_paths.docx'
        );

        $this->assertSame(
            [
                WordDocument::getContentPath(),
                'word/header1.xml',
                'word/footer1.xml',
            ],
            $document->getContentPaths()
        );
    }
}
