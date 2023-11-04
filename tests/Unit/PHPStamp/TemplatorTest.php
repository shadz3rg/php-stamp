<?php

namespace PHPStamp\Tests\Unit\PHPStamp;

use PHPStamp\Document\DocumentInterface;
use PHPStamp\Document\WordDocument;
use PHPStamp\Templator;
use PHPStamp\Tests\BaseCase;

class TemplatorTest extends BaseCase
{
    /**
     * @dataProvider
     * @return array
     */
    public function renderProvider(): array
    {
        return [
            // https://learn.microsoft.com/ru-ru/office/open-xml/structure-of-a-wordprocessingml-document
            'base case' => [
                '<?xml version="1.0" encoding="UTF-8"?>'.
                '<w:document xmlns:w="https://schemas.openxmlformats.org/wordprocessingml/2006/main">'.
                '  <w:body>'.
                '    <w:p>'.
                '      <w:r>'.
                '        <w:t>Hello, [[username]]!</w:t>'.
                '      </w:r>'.
                '    </w:p>'.
                '  </w:body>'.
                '</w:document>',
                [
                    'username' => 'Neo'
                ],
                '<?xml version="1.0" encoding="UTF-8"?>'. PHP_EOL .
                '<w:document xmlns:w="https://schemas.openxmlformats.org/wordprocessingml/2006/main">'.
                '  <w:body>'.
                '    <w:p>'.
                '      <w:r>'.
                '        <w:t xml:space="preserve">Hello, Neo!</w:t>'.
                '      </w:r>'.
                '    </w:p>'.
                '  </w:body>'.
                '</w:document>'. PHP_EOL
            ]
        ];
    }

    /**
     * @dataProvider renderProvider
     */
    public function testRender(string $content, array $values, string $expected): void
    {
        $templator = new Templator(sys_get_temp_dir() . DIRECTORY_SEPARATOR);
        $templator->debug = true;

        $document = $this->makeMockDocument($content, WordDocument::class, 'test1.docx');
        $result = $templator->render($document, $values);

        $expected = str_replace('  ', '', $expected); // remove indentation
        $this->assertEquals($expected, $result->getContent()->saveXML());
    }
}
