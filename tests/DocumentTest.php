<?php
namespace tests;

use OfficeML\Processor;
use OfficeML\Document\BasicDocument;

class DocumentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_collect_tokens()
    {
        $filePath = __DIR__ . '/../static/document.xml';
        $cachePath = __DIR__ . '/../static/cache/';

        $document = new BasicDocument($filePath);
        $documentFile = $document->extract($cachePath, true);

        $template = new \DOMDocument('1.0', 'UTF-8');
        $template->preserveWhiteSpace = false; // TODO NESSESARY
        $template->load($documentFile);

        // find them all
        $tokenCollection = $document->getTokenCollection($template);
        $this->assertEquals(8, $tokenCollection->count());

        // strip them all
        $processor = new Processor(array('[[', ']]'));
        $processor->wrapIntoTemplate($template);
        $processor->insertTemplateLogic($template, $tokenCollection);

        $template->formatOutput = true;
        $compare = $template->saveXML();
        $against = '<document><paragraph><run><text>Привет </text></run><run><text></text></run><run><text>!</text></run></paragraph><paragraph><run><text>Мы протестируем  </text></run><run><text>с помощью </text></run><run><text></text></run></paragraph><paragraph><run><text>Wish me</text></run><run><text></text></run><run><text>!</text></run></paragraph><paragraph><run><text>Данный </text></run><run><text></text></run><run><text>- полный бред</text></run></paragraph><paragraph><run><text></text></run><run><text></text></run><run><text></text></run></paragraph></document>';
        //$this->assertEquals($against, $compare);

echo $compare;


    }
}
 