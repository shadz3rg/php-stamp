<?php

namespace PHPStamp\Tests\Unit\PHPStamp;

use PHPStamp\Exception\ParsingException;
use PHPStamp\Tests\BaseCase;
use PHPStamp\XMLHelper;

class XMLHelperTest extends BaseCase
{
    /**
     * @dataProvider
     * @return array
     */
    public function deepEqualProvider(): array
    {
        return [
            'base case' => [
                '<?xml version="1.0" encoding="UTF-8"?>'.
                '<root>'.
                '    <div id="abc">123<span>A</span></div>'.
                '    <div id="abc">123<span>A</span></div>'.
                '</root>',
                true
            ],
            'should compare namespace' => [
                '<?xml version="1.0" encoding="UTF-8"?>'.
                '<root xmlns:html="http://www.w3.org/TR/html4/">'.
                '    <html:span>123</html:span>'.
                '    <span>123</span>'.
                '</root>',
                false
            ],
            'should compare value' => [
                '<?xml version="1.0" encoding="UTF-8"?>'.
                '<root>'.
                '    <span>123</span>'.
                '    <span>321</span>'.
                '</root>',
                false
            ],
            'should compare attribute count' => [
                '<?xml version="1.0" encoding="UTF-8"?>'.
                '<root>'.
                '    <span id="1">123</span>'.
                '    <span>321</span>'.
                '</root>',
                false
            ],
            'should compare attribute value' => [
                '<?xml version="1.0" encoding="UTF-8"?>'.
                '<root>'.
                '    <span id="1">123</span>'.
                '    <span id="2">321</span>'.
                '</root>',
                false
            ],
            'should compare attribute presence' => [
                '<?xml version="1.0" encoding="UTF-8"?>'.
                '<root>'.
                '    <span id="1">123</span>'.
                '    <span id="1" style="y">321</span>'.
                '</root>',
                false
            ],
            'should compare child nodes' => [
                '<?xml version="1.0" encoding="UTF-8"?>'.
                '<root>'.
                '    <div><div>123</div></div>'.
                '    <div><div>123</div><div>321</div></div>'.
                '</root>',
                false
            ],
            'should compare recursively' => [
                '<?xml version="1.0" encoding="UTF-8"?>'.
                '<root>'.
                '    <div id="A"><div id="B"><div id="C1">123</div><div id="C2">123</div></div></div>'.
                '    <div id="A"><div id="B"><div id="C1">123</div><div id="C2">123</div></div></div>'.
                '</root>',
                true
            ]
        ];
    }

    /**
     * @dataProvider deepEqualProvider
     */
    public function testDeepEqual(string $content, bool $expected): void
    {
        $document = new \DOMDocument('1.0', 'UTF-8');
        $document->loadXML($content);

        /** @var \DOMElement $root */
        $root = $document->documentElement;
        $this->assertEquals('root', $root->nodeName);

        /** @var \DOMNodeList $rootChilds */
        $rootChilds = $root->childNodes;
        $this->assertEquals(4, $rootChilds->count()); // DOM_TEXT included

        list(, $a, , $b) = iterator_to_array($rootChilds);

        $helper = new XMLHelper();
        $this->assertEquals($expected, $helper->deepEqual($a, $b));
    }

    public function testQueryTemplate(): void
    {
        $xml = <<<EOT
<?xml version="1.0"?>
<catalog>
   <book id="bk101">
      <author>Gambardella, Matthew</author>
      <title>XML Developer's Guide</title>
      <genre>Computer</genre>
      <price>44.95</price>
      <publish_date>2000-10-01</publish_date>
      <description>An in-depth look at creating applications 
      with XML.</description>
   </book>
</catalog>
EOT;

        $document = new \DOMDocument();
        $document->loadXML($xml);

        $result = XMLHelper::queryTemplate($document, '/catalog/book[@id="bk101"]/genre');
        $this->assertInstanceOf(\DOMNodeList::class, $result);
        $this->assertEquals(1, $result->count());
        $this->assertEquals('Computer', $result->item(0)->nodeValue);
    }

    /**
     * @dataProvider
     * @return array
     */
    public function parentUntilProvider(): array
    {
        $xml = <<<EOT
<?xml version="1.0"?>
<catalog>
   <book id="bk101">
      <author>Gambardella, Matthew</author>
      <title>XML Developer's Guide</title>
      <genre>Computer</genre>
      <price>44.95</price>
      <publish_date>2000-10-01</publish_date>
      <description>An in-depth look at creating applications 
      with XML.</description>
   </book>
</catalog>
EOT;

        $document = new \DOMDocument();
        $document->loadXML($xml);

        $titles = $document->getElementsByTagName('title');
        $node = $titles->item(0);

        return [
            'parent case' => ["book", $node, null],
            'recursive case' => ["catalog", $node, null],
            'not found case' => ["unknown", $node, ParsingException::class],
        ];
    }

    /**
     * @dataProvider parentUntilProvider
     * @throws ParsingException
     */
    public function testParentUntil(string $nodeName, \DOMNode $node, ?string $exception = null): void
    {
        if ($exception !== null) {
            $this->expectException($exception);
        }

        $found = XMLHelper::parentUntil($nodeName, $node);
        $this->assertInstanceOf(\DOMNode::class, $found);
        $this->assertEquals($nodeName, $found->nodeName);
    }

    /**
     * @dataProvider
     * @return array
     */
    public function encodeProvider(): array
    {
        return [
            'key value case' => [
                ['test' => 123, 'test2' => 'Hello'],
                'root',
                '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL.
                '<root>'.
                '    <test>123</test>'.
                '    <test2>Hello</test2>'.
                '</root>'.PHP_EOL
            ],
            'recursive tree case' => [
                ['test' => 1, 'tier0' => ['test' => 2, 'tier1' => ['test' => 3, 'tier2' => 'Hello']]],
                'root',
                '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL.
                '<root>'.
                '    <test>1</test>'.
                '    <tier0>'.
                '        <test>2</test>'.
                '        <tier1>'.
                '            <test>3</test>'.
                '            <tier2>Hello</tier2>'.
                '        </tier1>'.
                '    </tier0>'.
                '</root>'.PHP_EOL
            ],
            'list case' => [
                ['test' => ['test1', 'test2', 'test3']],
                'root',
                '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL.
                '<root>'.
                '    <test>'.
                '        <item>test1</item>'.
                '        <item>test2</item>'.
                '        <item>test3</item>'.
                '    </test>'.
                '</root>'.PHP_EOL,
            ],
            // TODO 'stringified case' => [],
            // TODO 'non stringified case' => [],
        ];
    }

    /**
     * @dataProvider encodeProvider
     */
    public function testEncode(array $data, string $root, string $expected): void
    {
        $document = new \DOMDocument('1.0', 'UTF-8');

        $tokensNode = $document->createElement($root);
        $document->appendChild($tokensNode);
        XMLHelper::xmlEncode($data, $tokensNode, $document);

        $expected = str_replace('    ', '', $expected); // remove indentation
        $this->assertEquals($expected, $document->saveXML());
    }
}
