<?php

namespace tests;

use OfficeML\Document\WriterDocument\Cleanup;

class CleanupTest extends \PHPUnit_Framework_TestCase
{
    public function test_hardcore_cleanup()
    {
        $xml = '<root xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0">
                <style:style style:name="T4" style:parent-style-name="Основнойшрифтабзаца" style:family="text">
                  <style:text-properties fo:font-weight="bold" style:font-weight-asian="bold" />
                </style:style>
                <style:style style:name="T5" style:parent-style-name="Основнойшрифтабзаца" style:family="text">
                  <style:text-properties fo:font-weight="bold" style:font-weight-asian="bold" fo:language="en" fo:country="US" />
                </style:style>
                <style:style style:name="T6" style:parent-style-name="Основнойшрифтабзаца" style:family="text">
                  <style:text-properties fo:font-weight="bold" style:font-weight-asian="bold" />
                </style:style>
                <style:style style:name="T7" style:parent-style-name="Основнойшрифтабзаца" style:family="text">
                  <style:text-properties fo:font-weight="bold" style:font-weight-asian="bold" />
                </style:style>
                </root>';
        $document = new \DOMDocument();
        $document->loadXML($xml);

        $cleanup = new Cleanup($document);
        $cleanup->hardcoreCleanup();

        $xml = '<root xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0">
                <style:style style:name="T4" style:parent-style-name="Основнойшрифтабзаца" style:family="text">
                  <style:text-properties fo:font-weight="bold" style:font-weight-asian="bold" />
                </style:style>
                <style:style style:name="T5" style:parent-style-name="Основнойшрифтабзаца" style:family="text">
                  <style:text-properties fo:font-weight="bold" style:font-weight-asian="bold" />
                </style:style>
                <style:style style:name="T6" style:parent-style-name="Основнойшрифтабзаца" style:family="text">
                  <style:text-properties fo:font-weight="bold" style:font-weight-asian="bold" />
                </style:style>
                <style:style style:name="T7" style:parent-style-name="Основнойшрифтабзаца" style:family="text">
                  <style:text-properties fo:font-weight="bold" style:font-weight-asian="bold" />
                </style:style>
                </root>';
        $compareAgainst = new \DOMDocument();
        $compareAgainst->loadXML($xml);

        $this->assertXmlStringEqualsXmlString($compareAgainst->saveXML(), $document->saveXML());
    }

    public function test_equalityMap()
    {
        $xml = '<root xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0">
                    <style:style style:name="T4" style:parent-style-name="Основнойшрифтабзаца" style:family="text">
                      <style:text-properties fo:font-weight="bold" style:font-weight-asian="normal" />
                    </style:style>
                    <style:style style:name="T5" style:parent-style-name="Основнойшрифтабзаца" style:family="text">
                      <style:text-properties fo:font-weight="bold" style:font-weight-asian="bold" />
                    </style:style>
                    <style:style style:name="T6" style:parent-style-name="Основнойшрифтабзаца" style:family="text">
                      <style:text-properties fo:font-weight="bold" style:font-weight-asian="bold" />
                    </style:style>
                    <style:style style:name="T7" style:parent-style-name="Основнойшрифтабзаца" style:family="text">
                      <style:text-properties fo:font-weight="bold" style:font-weight-asian="normal" />
                    </style:style>
                    <style:style style:name="T8" style:parent-style-name="Основнойшрифтабзаца" style:family="text">
                      <style:text-properties fo:font-weight="bold" style:font-weight-asian="normal" />
                    </style:style>
                    <style:style style:name="T9" style:parent-style-name="Основнойшрифтабзаца" style:family="text">
                      <style:text-properties fo:font-weight="bold" style:font-weight-asian="normal2" />
                    </style:style>
                </root>';
        $document = new \DOMDocument();
        $document->loadXML($xml);

        $xpath = new \DOMXPath($document);
        $nodeList = $xpath->query('//style:style');

        $cleanup = new Cleanup($document);
        $map = $cleanup->equalityMap($nodeList);

        $expected = array(
            array('T4', 'T7', 'T8'),
            array('T5', 'T6'),
            array('T9')
        );

        $this->assertEquals($expected, $map);
    }

    public function test_grouping()
    {
        // prepare
        $xml = '<root xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0">
                    <text:p text:style-name="P1">
                        <text:span text:style-name="T2">Привет</text:span>
                        <text:span text:style-name="T3">
                          <text:s />
                        </text:span>
                        <text:span text:style-name="T4">[[</text:span>
                        <text:span text:style-name="T5">username</text:span>
                        <text:span text:style-name="T6">]]</text:span>
                        <text:span text:style-name="T7">.</text:span>
                     </text:p>
                </root>';
        $document = new \DOMDocument();
        $document->loadXML($xml);
        $xpath = new \DOMXpath($document);
        $nodeList = $xpath->query('//text:p');

        $equalityMap = array(
            array('P1', 'T2', 'T3', 'T4', 'T5', 'T6'),
            array('T7')
        );

        $method = new \ReflectionMethod(
            'OfficeML\Document\WriterDocument\Cleanup', 'groupText'
        );
        $method->setAccessible(true);
        $method->invoke(new Cleanup($document), $nodeList, $equalityMap);

        $xml = '<root xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0">
                    <text:p text:style-name="P1">
                        Привет<text:s />[[username]]
                        <text:span text:style-name="T7">.</text:span>
                     </text:p>
                </root>';
        $compareAgainst = new \DOMDocument();
        $compareAgainst->loadXML($xml);

        $this->assertXmlStringEqualsXmlString($compareAgainst->saveXML(), $document->saveXML());
    }
}
 