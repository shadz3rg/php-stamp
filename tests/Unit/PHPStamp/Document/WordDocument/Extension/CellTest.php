<?php

namespace PHPStamp\Tests\Unit\PHPStamp\Document\WordDocument\Extension;

use PHPStamp\Document\WordDocument\Extension\Cell;
use PHPStamp\Processor\Lexer;
use PHPStamp\Processor\TagMapper;
use PHPStamp\Tests\BaseCase;

class CellTest extends BaseCase
{
    /**
     * @dataProvider
     * @return array<string,mixed>
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
                '        <w:tbl>'.
                '          <w:tblPr>'.
                '            <w:tblStyle w:val="a3"/>'.
                '            <w:tblW w:w="9389" w:type="dxa"/>'.
                '            <w:tblLook w:val="04A0" w:firstRow="1" w:lastRow="0" w:firstColumn="1" w:lastColumn="0" w:noHBand="0" w:noVBand="1"/>'.
                '          </w:tblPr>'.
                '          <w:tblGrid>'.
                '            <w:gridCol w:w="2347"/>'.
                '            <w:gridCol w:w="2347"/>'.
                '            <w:gridCol w:w="2347"/>'.
                '            <w:gridCol w:w="2348"/>'.
                '          </w:tblGrid>'.
                '          <w:tr w:rsidR="00D2002E" w:rsidTr="0024614C">'.
                '            <w:trPr>'.
                '              <w:trHeight w:val="262"/>'.
                '            </w:trPr>'.
                '            <w:tc>'.
                '              <w:tcPr>'.
                '                <w:tcW w:w="2347" w:type="dxa"/>'.
                '              </w:tcPr>'.
                '              <w:p w:rsidR="00D2002E" w:rsidRDefault="00D2002E" w:rsidP="0024614C">'.
                '                <w:pPr>'.
                '                  <w:rPr>'.
                '                    <w:lang w:val="en-US"/>'.
                '                  </w:rPr>'.
                '                </w:pPr>'.
                '                <w:r>'.
                '                  <w:rPr>'.
                '                    <w:lang w:val="en-US"/>'.
                '                  </w:rPr>'.
                '                  <w:t>ID</w:t>'.
                '                </w:r>'.
                '              </w:p>'.
                '            </w:tc>'.
                '            <w:tc>'.
                '              <w:tcPr>'.
                '                <w:tcW w:w="2347" w:type="dxa"/>'.
                '              </w:tcPr>'.
                '              <w:p w:rsidR="00D2002E" w:rsidRDefault="00D2002E" w:rsidP="0024614C">'.
                '                <w:pPr>'.
                '                  <w:rPr>'.
                '                    <w:lang w:val="en-US"/>'.
                '                  </w:rPr>'.
                '                </w:pPr>'.
                '                <w:r>'.
                '                  <w:rPr>'.
                '                    <w:lang w:val="en-US"/>'.
                '                  </w:rPr>'.
                '                  <w:t>Student</w:t>'.
                '                </w:r>'.
                '              </w:p>'.
                '            </w:tc>'.
                '            <w:tc>'.
                '              <w:tcPr>'.
                '                <w:tcW w:w="2347" w:type="dxa"/>'.
                '              </w:tcPr>'.
                '              <w:p w:rsidR="00D2002E" w:rsidRDefault="00D2002E" w:rsidP="0024614C">'.
                '                <w:pPr>'.
                '                  <w:rPr>'.
                '                    <w:lang w:val="en-US"/>'.
                '                  </w:rPr>'.
                '                </w:pPr>'.
                '                <w:r>'.
                '                  <w:rPr>'.
                '                    <w:lang w:val="en-US"/>'.
                '                  </w:rPr>'.
                '                  <w:t>Mark</w:t>'.
                '                </w:r>'.
                '              </w:p>'.
                '            </w:tc>'.
                '            <w:tc>'.
                '              <w:tcPr>'.
                '                <w:tcW w:w="2348" w:type="dxa"/>'.
                '              </w:tcPr>'.
                '              <w:p w:rsidR="00D2002E" w:rsidRDefault="00D2002E" w:rsidP="0024614C">'.
                '                <w:pPr>'.
                '                  <w:rPr>'.
                '                    <w:lang w:val="en-US"/>'.
                '                  </w:rPr>'.
                '                </w:pPr>'.
                '                <w:r>'.
                '                  <w:rPr>'.
                '                    <w:lang w:val="en-US"/>'.
                '                  </w:rPr>'.
                '                  <w:t>Max Mark</w:t>'.
                '                </w:r>'.
                '              </w:p>'.
                '            </w:tc>'.
                '          </w:tr>'.
                '          <w:tr w:rsidR="00D2002E" w:rsidTr="0024614C">'.
                '            <w:trPr>'.
                '              <w:trHeight w:val="247"/>'.
                '            </w:trPr>'.
                '            <w:tc>'.
                '              <w:tcPr>'.
                '                <w:tcW w:w="2347" w:type="dxa"/>'.
                '              </w:tcPr>'.
                '              <w:p w:rsidR="00D2002E" w:rsidRDefault="00D2002E" w:rsidP="0024614C">'.
                '                <w:pPr>'.
                '                  <w:rPr>'.
                '                    <w:lang w:val="en-US"/>'.
                '                  </w:rPr>'.
                '                </w:pPr>'.
                '                <w:r>'.
                '                  <w:rPr>'.
                '                    <w:lang w:val="en-US"/>'.
                '                  </w:rPr>'.
                '                  <w:t>[[id:cell(students)]]</w:t>'.
                '                </w:r>'.
                '              </w:p>'.
                '            </w:tc>'.
                '            <w:tc>'.
                '              <w:tcPr>'.
                '                <w:tcW w:w="2347" w:type="dxa"/>'.
                '              </w:tcPr>'.
                '              <w:p w:rsidR="00D2002E" w:rsidRDefault="00D2002E" w:rsidP="0024614C">'.
                '                <w:pPr>'.
                '                  <w:rPr>'.
                '                    <w:lang w:val="en-US"/>'.
                '                  </w:rPr>'.
                '                </w:pPr>'.
                '                <w:r>'.
                '                  <w:rPr>'.
                '                    <w:lang w:val="en-US"/>'.
                '                  </w:rPr>'.
                '                  <w:t>[[name:cell(students)]]</w:t>'.
                '                </w:r>'.
                '              </w:p>'.
                '            </w:tc>'.
                '            <w:tc>'.
                '              <w:tcPr>'.
                '                <w:tcW w:w="2347" w:type="dxa"/>'.
                '              </w:tcPr>'.
                '              <w:p w:rsidR="00D2002E" w:rsidRDefault="00D2002E" w:rsidP="0024614C">'.
                '                <w:pPr>'.
                '                  <w:rPr>'.
                '                    <w:lang w:val="en-US"/>'.
                '                  </w:rPr>'.
                '                </w:pPr>'.
                '                <w:r>'.
                '                  <w:rPr>'.
                '                    <w:lang w:val="en-US"/>'.
                '                  </w:rPr>'.
                '                  <w:t>[[mark:cell(students)]]</w:t>'.
                '                </w:r>'.
                '              </w:p>'.
                '            </w:tc>'.
                '            <w:tc>'.
                '              <w:tcPr>'.
                '                <w:tcW w:w="2348" w:type="dxa"/>'.
                '              </w:tcPr>'.
                '              <w:p w:rsidR="00D2002E" w:rsidRDefault="00D2002E" w:rsidP="0024614C">'.
                '                <w:pPr>'.
                '                  <w:rPr>'.
                '                    <w:lang w:val="en-US"/>'.
                '                  </w:rPr>'.
                '                </w:pPr>'.
                '                <w:r>'.
                '                  <w:rPr>'.
                '                    <w:lang w:val="en-US"/>'.
                '                  </w:rPr>'.
                '                  <w:t>[[maxMark]]</w:t>'.
                '                </w:r>'.
                '              </w:p>'.
                '            </w:tc>'.
                '          </w:tr>'.
                '        </w:tbl>'.
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
                '        <w:tbl>'.
                '          <w:tblPr>'.
                '            <w:tblStyle w:val="a3"/>'.
                '            <w:tblW w:w="9389" w:type="dxa"/>'.
                '            <w:tblLook w:val="04A0" w:firstRow="1" w:lastRow="0" w:firstColumn="1" w:lastColumn="0" w:noHBand="0" w:noVBand="1"/>'.
                '          </w:tblPr>'.
                '          <w:tblGrid>'.
                '            <w:gridCol w:w="2347"/>'.
                '            <w:gridCol w:w="2347"/>'.
                '            <w:gridCol w:w="2347"/>'.
                '            <w:gridCol w:w="2348"/>'.
                '          </w:tblGrid>'.
                '          <w:tr w:rsidR="00D2002E" w:rsidTr="0024614C">'.
                '            <w:trPr>'.
                '              <w:trHeight w:val="262"/>'.
                '            </w:trPr>'.
                '            <w:tc>'.
                '              <w:tcPr>'.
                '                <w:tcW w:w="2347" w:type="dxa"/>'.
                '              </w:tcPr>'.
                '              <w:p w:rsidR="00D2002E" w:rsidRDefault="00D2002E" w:rsidP="0024614C">'.
                '                <w:pPr>'.
                '                  <w:rPr>'.
                '                    <w:lang w:val="en-US"/>'.
                '                  </w:rPr>'.
                '                </w:pPr>'.
                '                <w:r>'.
                '                  <w:rPr>'.
                '                    <w:lang w:val="en-US"/>'.
                '                  </w:rPr>'.
                '                  <w:t>ID</w:t>'.
                '                </w:r>'.
                '              </w:p>'.
                '            </w:tc>'.
                '            <w:tc>'.
                '              <w:tcPr>'.
                '                <w:tcW w:w="2347" w:type="dxa"/>'.
                '              </w:tcPr>'.
                '              <w:p w:rsidR="00D2002E" w:rsidRDefault="00D2002E" w:rsidP="0024614C">'.
                '                <w:pPr>'.
                '                  <w:rPr>'.
                '                    <w:lang w:val="en-US"/>'.
                '                  </w:rPr>'.
                '                </w:pPr>'.
                '                <w:r>'.
                '                  <w:rPr>'.
                '                    <w:lang w:val="en-US"/>'.
                '                  </w:rPr>'.
                '                  <w:t>Student</w:t>'.
                '                </w:r>'.
                '              </w:p>'.
                '            </w:tc>'.
                '            <w:tc>'.
                '              <w:tcPr>'.
                '                <w:tcW w:w="2347" w:type="dxa"/>'.
                '              </w:tcPr>'.
                '              <w:p w:rsidR="00D2002E" w:rsidRDefault="00D2002E" w:rsidP="0024614C">'.
                '                <w:pPr>'.
                '                  <w:rPr>'.
                '                    <w:lang w:val="en-US"/>'.
                '                  </w:rPr>'.
                '                </w:pPr>'.
                '                <w:r>'.
                '                  <w:rPr>'.
                '                    <w:lang w:val="en-US"/>'.
                '                  </w:rPr>'.
                '                  <w:t>Mark</w:t>'.
                '                </w:r>'.
                '              </w:p>'.
                '            </w:tc>'.
                '            <w:tc>'.
                '              <w:tcPr>'.
                '                <w:tcW w:w="2348" w:type="dxa"/>'.
                '              </w:tcPr>'.
                '              <w:p w:rsidR="00D2002E" w:rsidRDefault="00D2002E" w:rsidP="0024614C">'.
                '                <w:pPr>'.
                '                  <w:rPr>'.
                '                    <w:lang w:val="en-US"/>'.
                '                  </w:rPr>'.
                '                </w:pPr>'.
                '                <w:r>'.
                '                  <w:rPr>'.
                '                    <w:lang w:val="en-US"/>'.
                '                  </w:rPr>'.
                '                  <w:t>Max Mark</w:t>'.
                '                </w:r>'.
                '              </w:p>'.
                '            </w:tc>'.
                '          </w:tr>'.
                '          <xsl:for-each select="/values/students/item">'.
                '            <xsl:call-template name="students"/>'.
                '          </xsl:for-each>'.
                '        </w:tbl>'.
                '      </w:body>'.
                '    </w:document>'.
                '  </xsl:template>'.
                '  <xsl:template name="students">'.
                '    <w:tr xmlns:w="https://schemas.openxmlformats.org/wordprocessingml/2006/main" w:rsidR="00D2002E" w:rsidTr="0024614C">'.
                '      <w:trPr>'.
                '        <w:trHeight w:val="247"/>'.
                '      </w:trPr>'.
                '      <w:tc>'.
                '        <w:tcPr>'.
                '          <w:tcW w:w="2347" w:type="dxa"/>'.
                '        </w:tcPr>'.
                '        <w:p w:rsidR="00D2002E" w:rsidRDefault="00D2002E" w:rsidP="0024614C">'.
                '          <w:pPr>'.
                '            <w:rPr>'.
                '              <w:lang w:val="en-US"/>'.
                '            </w:rPr>'.
                '          </w:pPr>'.
                '          <w:r>'.
                '            <w:rPr>'.
                '              <w:lang w:val="en-US"/>'.
                '            </w:rPr>'.
                '            <w:t xml:space="preserve"><xsl:value-of select="id"/></w:t>'.
                '          </w:r>'.
                '        </w:p>'.
                '      </w:tc>'.
                '      <w:tc>'.
                '        <w:tcPr>'.
                '          <w:tcW w:w="2347" w:type="dxa"/>'.
                '        </w:tcPr>'.
                '        <w:p w:rsidR="00D2002E" w:rsidRDefault="00D2002E" w:rsidP="0024614C">'.
                '          <w:pPr>'.
                '            <w:rPr>'.
                '              <w:lang w:val="en-US"/>'.
                '            </w:rPr>'.
                '          </w:pPr>'.
                '          <w:r>'.
                '            <w:rPr>'.
                '              <w:lang w:val="en-US"/>'.
                '            </w:rPr>'.
                '            <w:t xml:space="preserve"><xsl:value-of select="name"/></w:t>'.
                '          </w:r>'.
                '        </w:p>'.
                '      </w:tc>'.
                '      <w:tc>'.
                '        <w:tcPr>'.
                '          <w:tcW w:w="2347" w:type="dxa"/>'.
                '        </w:tcPr>'.
                '        <w:p w:rsidR="00D2002E" w:rsidRDefault="00D2002E" w:rsidP="0024614C">'.
                '          <w:pPr>'.
                '            <w:rPr>'.
                '              <w:lang w:val="en-US"/>'.
                '            </w:rPr>'.
                '          </w:pPr>'.
                '          <w:r>'.
                '            <w:rPr>'.
                '              <w:lang w:val="en-US"/>'.
                '            </w:rPr>'.
                '            <w:t xml:space="preserve"><xsl:value-of select="mark"/></w:t>'.
                '          </w:r>'.
                '        </w:p>'.
                '      </w:tc>'.
                '      <w:tc>'.
                '        <w:tcPr>'.
                '          <w:tcW w:w="2348" w:type="dxa"/>'.
                '        </w:tcPr>'.
                '        <w:p w:rsidR="00D2002E" w:rsidRDefault="00D2002E" w:rsidP="0024614C">'.
                '          <w:pPr>'.
                '            <w:rPr>'.
                '              <w:lang w:val="en-US"/>'.
                '            </w:rPr>'.
                '          </w:pPr>'.
                '          <w:r>'.
                '            <w:rPr>'.
                '              <w:lang w:val="en-US"/>'.
                '            </w:rPr>'.
                '            <w:t>[[maxMark]]</w:t>'.
                '          </w:r>'.
                '        </w:p>'.
                '      </w:tc>'.
                '    </w:tr>'.
                '  </xsl:template>'.
                '</xsl:stylesheet>'. PHP_EOL,
            ]
        ];
    }

    /**
     * @dataProvider executeProvider
     */
    public function testExecuteC(string $content, string $expected): void
    {
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->loadXML(str_replace('  ', '', $content));

        $nodeList = $doc->getElementsByTagName('t');
        $this->assertEquals(8, $nodeList->count());

        $lexer = new Lexer(['[[', ']]']);

        /** @var \DOMElement $node */
        foreach ($nodeList as $node) {
            $this->assertNotNull($node->nodeValue);
            $lexer->setInput($node->nodeValue);
            $mapper = new TagMapper();
            $tag = $mapper->parse($lexer);

            if ($tag !== null) {
                if (count($tag->getFunctions()) !== 0) {
                    $ext = new Cell($tag);
                    $ext->execute($tag->getFunctions()[0]['arguments'], $node);
                }
            }
        }

        $this->assertEquals(str_replace('  ', '', $expected), $doc->saveXML());
    }
}
