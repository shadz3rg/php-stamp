<?php

namespace PHPStamp\Tests\Unit\PHPStamp;

use PHPStamp\Document\WordDocument;
use PHPStamp\Templator;
use PHPStamp\Tests\BaseCase;

class TemplatorTest extends BaseCase
{
    /**
     * @dataProvider
     *
     * @return array<string,mixed>
     */
    public function renderContentProvider(): array
    {
        $studentsDoc = new \ZipArchive();
        $studentsDoc->open(__DIR__.'/../../resources/students.docx');

        $studentsResultDoc = new \ZipArchive();
        $studentsResultDoc->open(__DIR__.'/../../resources/students_result.docx');

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
                    'username' => 'Neo',
                ],
                '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL.
                '<w:document xmlns:w="https://schemas.openxmlformats.org/wordprocessingml/2006/main">'.
                '  <w:body>'.
                '    <w:p>'.
                '      <w:r>'.
                '        <w:t xml:space="preserve">Hello, Neo!</w:t>'.
                '      </w:r>'.
                '    </w:p>'.
                '  </w:body>'.
                '</w:document>'.PHP_EOL,
            ],
            'line break case' => [
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
                    'username' => implode('  ', ['Line 1', 'Line 2', 'Line 3']),
                ],
                '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL.
                '<w:document xmlns:w="https://schemas.openxmlformats.org/wordprocessingml/2006/main">'.
                '  <w:body>'.
                '    <w:p>'.
                '      <w:r>'.
                '        <w:t xml:space="preserve">Hello, </w:t>'.
                '        <w:t xml:space="preserve">Line 1</w:t>'.
                '        <w:br/>'.
                '        <w:t xml:space="preserve">Line 2</w:t>'.
                '        <w:br/>'.
                '        <w:t xml:space="preserve">Line 3</w:t>'.
                '        <w:t xml:space="preserve">!</w:t>'.
                '      </w:r>'.
                '    </w:p>'.
                '  </w:body>'.
                '</w:document>'.PHP_EOL,
            ],
        ];
    }

    /**
     * @param array<string,string> $values
     *
     * @dataProvider renderContentProvider
     */
    public function testContentRender(string $content, array $values, string $expected): void
    {
        $templator = new Templator(sys_get_temp_dir().DIRECTORY_SEPARATOR);
        $templator->debug = true;

        $document = $this->makeMockDocument($content, WordDocument::class, 'test1.docx');
        $result = $templator->render($document, $values);

        $expected = str_replace('  ', '', $expected); // remove indentation
        $this->assertEquals($expected, $result->getContent()->saveXML());
    }

    /**
     * @dataProvider
     *
     * @return array<string,mixed>
     */
    public function renderFileProvider(): array
    {
        return [
            'students test' => [
                __DIR__.'/../../resources/students_libre.docx',
                [
                    'library' => 'PHPStamp 0.1',
                    'simpleValue' => 'I am simple value',
                    'nested' => [
                        'firstValue' => 'First child value',
                        'secondValue' => 'Second child value',
                    ],
                    'header' => 'test of a table row',
                    'students' => [
                        ['id' => 1, 'name' => 'Student 1', 'mark' => '10'],
                        ['id' => 2, 'name' => 'Student 2', 'mark' => '4'],
                        ['id' => 3, 'name' => 'Student 3', 'mark' => '7'],
                    ],
                    'maxMark' => 10,
                    'todo' => [
                        'TODO 1',
                        'TODO 2',
                        'TODO 3',
                    ],
                ],
                __DIR__.'/../../resources/students_libre_result.docx',
            ],
        ];
    }

    /**
     * @param array<string,string> $values
     *
     * @dataProvider renderFileProvider
     */
    public function testFileRender(string $contentFile, array $values, string $expectedFile): void
    {
        $templator = new Templator(sys_get_temp_dir().DIRECTORY_SEPARATOR);
        $templator->debug = true;

        $document = new WordDocument($contentFile);
        $result = $templator->render($document, $values);

        $zip = new \ZipArchive();
        $zip->open($expectedFile);
        $expected = $zip->getFromName(WordDocument::getContentPath());

        $this->assertEquals($expected, $result->getContent()->saveXML());
    }
}
