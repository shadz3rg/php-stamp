<?php

namespace PHPStamp\Tests\Unit\PHPStamp;

use PHPStamp\Document\DocumentInterface;
use PHPStamp\Processor;
use PHPStamp\Processor\Tag;
use PHPStamp\Tests\BaseCase;

class ProcessorTest extends BaseCase
{
    public function testWrapIntoTemplate()
    {
        $content = '<?xml version="1.0" encoding="UTF-8"?>'. PHP_EOL .
            '<w:document xmlns:w="https://schemas.openxmlformats.org/wordprocessingml/2006/main">'.
            '  <w:body>'.
            '    <w:p>'.
            '      <w:r>'.
            '        <w:t>Hello, [[username]]!</w:t>'.
            '      </w:r>'.
            '    </w:p>'.
            '  </w:body>'.
            '</w:document>'. PHP_EOL;

        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->loadXML(str_replace('  ', '', $content));

        $expected = '<?xml version="1.0" encoding="UTF-8"?>'. PHP_EOL .
            '<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:w="https://schemas.openxmlformats.org/wordprocessingml/2006/main" version="1.0">'.
            '  <xsl:output method="xml" encoding="UTF-8"/>'.
            '  <xsl:template xmlns:w="https://schemas.openxmlformats.org/wordprocessingml/2006/main" match="/values">'.
            '    <w:document xmlns:w="https://schemas.openxmlformats.org/wordprocessingml/2006/main">'.
            '      <w:body>'.
            '        <w:p>'.
            '          <w:r>'.
            '            <w:t>Hello, [[username]]!</w:t>'.
            '          </w:r>'.
            '        </w:p>'.
            '      </w:body>'.
            '    </w:document>'.
            '  </xsl:template>'.
            '</xsl:stylesheet>'. PHP_EOL;

        Processor::wrapIntoTemplate($doc);
        $this->assertEquals(str_replace('  ', '', $expected), $doc->saveXML());
    }

    public function testInsertTemplateLogic()
    {
        $template = '<?xml version="1.0" encoding="UTF-8"?>'. PHP_EOL .
            '<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:w="https://schemas.openxmlformats.org/wordprocessingml/2006/main" version="1.0">'.
            '  <xsl:output method="xml" encoding="UTF-8"/>'.
            '  <xsl:template xmlns:w="https://schemas.openxmlformats.org/wordprocessingml/2006/main" match="/values">'.
            '    <w:document xmlns:w="https://schemas.openxmlformats.org/wordprocessingml/2006/main">'.
            '      <w:body>'.
            '        <w:p>'.
            '          <w:r>'.
            '            <w:t>Hello, [[username]]!</w:t>'.
            '          </w:r>'.
            '        </w:p>'.
            '      </w:body>'.
            '    </w:document>'.
            '  </xsl:template>'.
            '</xsl:stylesheet>'. PHP_EOL;

        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->loadXML(str_replace('  ', '', $template));

        $nodeList = $doc->getElementsByTagName('t');
        $this->assertEquals(1, $nodeList->count());

        $node = $nodeList->item(0);
        $this->assertInstanceOf(\DOMElement::class, $node);

        $result = Processor::insertTemplateLogic('[[not_exist]]', '/values/username', $node);
        $this->assertFalse($result);

        $result = Processor::insertTemplateLogic('[[username]]', '/values/username', $node);
        $this->assertTrue($result);

        $expected = '<?xml version="1.0" encoding="UTF-8"?>'. PHP_EOL .
            '<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:w="https://schemas.openxmlformats.org/wordprocessingml/2006/main" version="1.0">'.
            '  <xsl:output method="xml" encoding="UTF-8"/>'.
            '  <xsl:template xmlns:w="https://schemas.openxmlformats.org/wordprocessingml/2006/main" match="/values">'.
            '    <w:document xmlns:w="https://schemas.openxmlformats.org/wordprocessingml/2006/main">'.
            '      <w:body>'.
            '        <w:p>'.
            '          <w:r>'.
            '            <w:t xml:space="preserve">Hello, <xsl:value-of select="/values/username"/>!</w:t>'.
            '          </w:r>'.
            '        </w:p>'.
            '      </w:body>'.
            '    </w:document>'.
            '  </xsl:template>'.
            '</xsl:stylesheet>'. PHP_EOL;

        $this->assertEquals(str_replace('  ', '', $expected), $doc->saveXML());
    }

    public function testEscapeXsl()
    {
        $content = '<?xml version="1.0" encoding="UTF-8"?>'. PHP_EOL .
            '<w:document xmlns:w="https://schemas.openxmlformats.org/wordprocessingml/2006/main">'.
            '  <w:body uri="{test=123}">'.
            '    <w:p>'.
            '      <w:r>'.
            '        <w:t>Hello, [[username]]!</w:t>'.
            '      </w:r>'.
            '    </w:p>'.
            '  </w:body>'.
            '</w:document>'. PHP_EOL;

        $expected = '<?xml version="1.0" encoding="UTF-8"?>'. PHP_EOL .
            '<w:document xmlns:w="https://schemas.openxmlformats.org/wordprocessingml/2006/main">'.
            '  <w:body uri="{{test=123}}">'.
            '    <w:p>'.
            '      <w:r>'.
            '        <w:t>Hello, [[username]]!</w:t>'.
            '      </w:r>'.
            '    </w:p>'.
            '  </w:body>'.
            '</w:document>'. PHP_EOL;

        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->loadXML(str_replace('  ', '', $content));

        Processor::escapeXsl($doc);
        $this->assertEquals(str_replace('  ', '', $expected), $doc->saveXML());
    }

    public function testUndoEscapeXsl()
    {
        $content = '<?xml version="1.0" encoding="UTF-8"?>'. PHP_EOL .
            '<w:document xmlns:w="https://schemas.openxmlformats.org/wordprocessingml/2006/main">'.
            '  <w:body uri="{{test=123}}">'.
            '    <w:p>'.
            '      <w:r>'.
            '        <w:t>Hello, [[username]]!</w:t>'.
            '      </w:r>'.
            '    </w:p>'.
            '  </w:body>'.
            '</w:document>'. PHP_EOL;

        $expected = '<?xml version="1.0" encoding="UTF-8"?>'. PHP_EOL .
            '<w:document xmlns:w="https://schemas.openxmlformats.org/wordprocessingml/2006/main">'.
            '  <w:body uri="{test=123}">'.
            '    <w:p>'.
            '      <w:r>'.
            '        <w:t>Hello, [[username]]!</w:t>'.
            '      </w:r>'.
            '    </w:p>'.
            '  </w:body>'.
            '</w:document>'. PHP_EOL;

        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->loadXML(str_replace('  ', '', $content));

        Processor::undoEscapeXsl($doc);
        $this->assertEquals(str_replace('  ', '', $expected), $doc->saveXML());
    }
}
 