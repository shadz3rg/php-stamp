<?php

namespace PHPStamp\Tests\Unit\PHPStamp\Document\WordDocument;

use PHPStamp\Document\WordDocument\LineBreaks;
use PHPStamp\Tests\BaseCase;

class LineBreaksTest extends BaseCase
{
    public function testReplace(): void
    {
        $content = '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL.
            '<w:document xmlns:w="https://schemas.openxmlformats.org/wordprocessingml/2006/main">'.
            '  <w:body>'.
            '    <w:p>'.
            '      <w:r>'.
            '        <w:t xml:space="preserve">Line 1'.PHP_EOL.'Line 2'.PHP_EOL.'Line 3</w:t>'.
            '      </w:r>'.
            '    </w:p>'.
            '  </w:body>'.
            '</w:document>'.PHP_EOL;

        $expected = '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL.
            '<w:document xmlns:w="https://schemas.openxmlformats.org/wordprocessingml/2006/main">'.
            '  <w:body>'.
            '    <w:p>'.
            '      <w:r>'.
            '        <w:t xml:space="preserve">Line 1</w:t>'.
            '        <w:br/>'.
            '        <w:t xml:space="preserve">Line 2</w:t>'.
            '        <w:br/>'.
            '        <w:t xml:space="preserve">Line 3</w:t>'.
            '      </w:r>'.
            '    </w:p>'.
            '  </w:body>'.
            '</w:document>'.PHP_EOL;

        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->loadXML(str_replace('  ', '', $content));

        LineBreaks::replace($doc);

        $this->assertEquals(str_replace('  ', '', $expected), $doc->saveXML());
    }
}
