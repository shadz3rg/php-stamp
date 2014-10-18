<?php

namespace tests;

use OfficeML\XMLHelper;

class XMLHelperTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_works()
    {
        // prepare xml
        $xml = '<root>
                    <child><pr><b/></pr></child>
                    <child><pr><b/></pr></child>
                </root>';
        $content = new \DOMDocument('1.0', 'UTF-8');
        $content->loadXML($xml);

        $xpath = new \DOMXPath($content);
        $nodeList = $xpath->query('/root/child');


        $helper = new XMLHelper();
        $result = $helper->deepEqual($nodeList->item(0), $nodeList->item(1), array());

        $this->assertTrue($result);
    }
}
 