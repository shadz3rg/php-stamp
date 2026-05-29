<?php

namespace PHPStamp\Tests\Unit\PHPStamp\Document;

use PHPStamp\Document\WordDocument;
use PHPStamp\Tests\BaseCase;

class DocumentTest extends BaseCase
{
    public function testExtractIgnoresTrailingSlash(): void
    {
        $this->assertExtractIgnoresTrailingSlash(true);
        $this->assertExtractIgnoresTrailingSlash(false);
    }

    private function assertExtractIgnoresTrailingSlash(bool $useTrailingSlash): void
    {
        $destinationPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'phpstamp-extract-'.uniqid('', true);
        mkdir($destinationPath);

        $extractPath = $destinationPath;
        if ($useTrailingSlash === true) {
            $extractPath .= DIRECTORY_SEPARATOR;
        }

        /** @var string */
        $content = file_get_contents(__DIR__.'/../../../resources/dummy.xml');
        $documentName = 'extract-path-'.uniqid('', true).'.docx';
        $document = $this->makeMockDocument($content, WordDocument::class, $documentName);

        $extractedFile = $document->extract($extractPath, true);

        $expectedContentFile = $destinationPath
            .DIRECTORY_SEPARATOR
            .$document->generateCacheKey()
            .DIRECTORY_SEPARATOR
            .str_replace('/', DIRECTORY_SEPARATOR, WordDocument::getContentPath());

        $this->assertSame($expectedContentFile, $extractedFile);
        $this->assertFileExists($expectedContentFile);
    }

    public function testCacheNameCollision(): void
    {
        $destinationPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'phpstamp-extract-'.uniqid('', true);
        mkdir($destinationPath);

        /** @var string $content */
        $content = file_get_contents(__DIR__.'/../../../resources/dummy.xml');
        $firstDocument = $this->makeMockDocumentAt($content, WordDocument::class, $this->makeDocumentPath('first', 'invoice.docx'));
        $secondDocument = $this->makeMockDocumentAt($content, WordDocument::class, $this->makeDocumentPath('second', 'invoice.docx'));

        $firstExtractedFile = $firstDocument->extract($destinationPath, true);
        $secondExtractedFile = $secondDocument->extract($destinationPath, true);

        $this->assertNotSame($firstDocument->generateCacheKey(), $secondDocument->generateCacheKey());
        $this->assertNotSame($firstExtractedFile, $secondExtractedFile);
        $this->assertFileExists($firstExtractedFile);
        $this->assertFileExists($secondExtractedFile);
    }

    private function makeDocumentPath(string $directoryName, string $filename): string
    {
        $dir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'docx-'.$directoryName.'-'.uniqid('', true);
        mkdir($dir);

        return $dir.DIRECTORY_SEPARATOR.$filename;
    }
}
