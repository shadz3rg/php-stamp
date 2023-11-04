<?php

namespace PHPStamp\Tests\Unit\PHPStamp\Document\WordDocument\Extension;

use PHPStamp\Document\WordDocument\Extension\ListItem;
use PHPStamp\Processor\Lexer;
use PHPStamp\Processor\Tag;
use PHPStamp\Processor\TagMapper;
use PHPStamp\Tests\BaseCase;

class ListItemTest extends BaseCase
{
    /**
     * @dataProvider
     * @return array
     */
    public function executeProvider(): array
    {
        return [
            'base case' => [
                '<?xml version="1.0" encoding="UTF-8"?>'. PHP_EOL .
                '<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:w="https://schemas.openxmlformats.org/wordprocessingml/2006/main" version="1.0">'.
                '  <xsl:output method="xml" encoding="UTF-8"/>'.
                '  <xsl:template xmlns:w="https://schemas.openxmlformats.org/wordprocessingml/2006/main" match="/values">'.
                '    <w:document xmlns:w="https://schemas.openxmlformats.org/wordprocessingml/2006/main">'.
                '      <w:body>'.
                '        <w:p>'.
                '          <w:r>'.
                '            <w:t>Hello, [[username:listitem()]]!</w:t>'.
                '          </w:r>'.
                '        </w:p>'.
                '      </w:body>'.
                '    </w:document>'.
                '  </xsl:template>'.
                '</xsl:stylesheet>'. PHP_EOL,

                '<?xml version="1.0" encoding="UTF-8"?>'. PHP_EOL .
                '<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:w="https://schemas.openxmlformats.org/wordprocessingml/2006/main" version="1.0">'.
                '  <xsl:output method="xml" encoding="UTF-8"/>'.
                '  <xsl:template xmlns:w="https://schemas.openxmlformats.org/wordprocessingml/2006/main" match="/values">'.
                '    <w:document xmlns:w="https://schemas.openxmlformats.org/wordprocessingml/2006/main">'.
                '      <w:body>'.
                '        <xsl:for-each select="/values/username/item">'.
                '          <xsl:call-template name="username"/>'.
                '        </xsl:for-each>'.
                '      </w:body>'.
                '    </w:document>'.
                '  </xsl:template>'.
                '  <xsl:template name="username">'.
                '    <w:p xmlns:w="https://schemas.openxmlformats.org/wordprocessingml/2006/main">'.
                '      <w:r>'.
                '        <w:t xml:space="preserve">Hello, <xsl:value-of select="."/>!</w:t>'.
                '      </w:r>'.
                '    </w:p>'.
                '  </xsl:template>'.
                '</xsl:stylesheet>'. PHP_EOL,
            ]
        ];
    }

    /**
     * @dataProvider executeProvider
     */
    public function testExecute(string $content, string $expected): void
    {
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->loadXML(str_replace('  ', '', $content));

        $nodeList = $doc->getElementsByTagName('t');
        $this->assertEquals(1, $nodeList->count());

        /** @var \DOMElement $node */
        $node = $nodeList->item(0);

        $lexer = new Lexer(['[[', ']]']);
        $lexer->setInput($node->nodeValue);

        $mapper = new TagMapper();
        $tag = $mapper->parse($lexer);
        $this->assertInstanceOf(Tag::class, $tag);

        $ext = new ListItem($tag);
        $ext->execute($tag->getFunctions()[0]['arguments'], $node);

        $this->assertEquals(str_replace('  ', '', $expected), $doc->saveXML());
    }
}
