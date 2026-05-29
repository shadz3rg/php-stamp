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
            .$documentName
            .DIRECTORY_SEPARATOR
            .str_replace('/', DIRECTORY_SEPARATOR, WordDocument::getContentPath());

        $this->assertSame($expectedContentFile, $extractedFile);
        $this->assertFileExists($expectedContentFile);
    }
}
