<?php
namespace tests;

use OfficeML\Processor\Tag;
use OfficeML\Processor;

class ProcessorTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_works()
    {
        // tag 1
        $tagData = array(
            'summary' => array(
                'textContent' => '[[iamtag]]',
                'position' => 5,
                'length' => 10
            ),
            'path' => array('iamtag'),
            'functions' => array()
        );
        $tag1 = new Tag($tagData['summary'], $tagData['path'], $tagData['functions']);

        // tag 2
        $tagData = array(
            'summary' => array(
                'textContent' => '[[xortag]]',
                'position' => 21,
                'length' => 10
            ),
            'path' => array('xortag'),
            'functions' => array()
        );
        $tag2 = new Tag($tagData['summary'], $tagData['path'], $tagData['functions']);

        // prepare xml
        $xml = '<supernode>The [[iamtag]] text [[xortag]] after</supernode>';
        $content = new \DOMDocument('1.0', 'UTF-8');
        $content->loadXML($xml);
        $node = $content->documentElement;

        // testing!
        $processor = new Processor;

        $processor->insertTemplateLogic($tag1, $node);
        $expectedString = '<supernode>The <xsl:value-of xmlns:xsl="http://www.w3.org/1999/XSL/Transform" select="/values/iamtag"/> text [[xortag]] after</supernode>';
        $this->assertEquals($expectedString, $content->saveXML($node));

        $processor->insertTemplateLogic($tag2, $node);
        $expectedString = '<supernode>The <xsl:value-of xmlns:xsl="http://www.w3.org/1999/XSL/Transform" select="/values/iamtag"/> text <xsl:value-of xmlns:xsl="http://www.w3.org/1999/XSL/Transform" select="/values/xortag"/> after</supernode>';
        $this->assertEquals($expectedString, $content->saveXML($node));
    }
}
 