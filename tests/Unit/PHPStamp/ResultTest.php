<?php

namespace PHPStamp\Tests\Unit\PHPStamp;

use PHPStamp\Document\WordDocument;
use PHPStamp\Result;
use PHPStamp\Tests\BaseCase;

class ResultTest extends BaseCase
{
    public function testBuildFile(): void
    {
        $content = '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL.
            '<w:document xmlns:w="https://schemas.openxmlformats.org/wordprocessingml/2006/main">'.
            '  <w:body>'.
            '    <w:p>'.
            '      <w:r>'.
            '        <w:t>Hello, [[username]]!</w:t>'.
            '      </w:r>'.
            '    </w:p>'.
            '  </w:body>'.
            '</w:document>'.PHP_EOL;

        $document = $this->makeMockDocument($content, WordDocument::class, 'test_build.docx');

        $rendered = '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL.
            '<w:document xmlns:w="https://schemas.openxmlformats.org/wordprocessingml/2006/main">'.
            '  <w:body>'.
            '    <w:p>'.
            '      <w:r>'.
            '        <w:t>Hello, Neo!</w:t>'.
            '      </w:r>'.
            '    </w:p>'.
            '  </w:body>'.
            '</w:document>'.PHP_EOL;

        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->loadXML($rendered);

        $result = new Result($doc, $document);
        $resultFile = $result->buildFile();
        $this->assertNotFalse($resultFile);
        $this->assertFileExists($resultFile);

        $zip = new \ZipArchive();
        $zip->open($resultFile);
        $this->assertEquals($rendered, $zip->getFromName($document->getContentPath()));
    }
}
