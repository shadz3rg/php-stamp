<?php

namespace PHPStamp\Tests\Unit\PHPStamp\Document\WordDocument;

use PHPStamp\Document\WordDocument\Cleanup;
use PHPStamp\Tests\BaseCase;

class CleanupTest extends BaseCase
{
    /**
     * @dataProvider
     *
     * @return array<string,mixed>
     */
    public function cleanupProvider(): array
    {
        return [
            'base case' => [
                <<<'EOD'
<?xml version="1.0" encoding="UTF-8"?>
<root>
    <a>
        <b>
            <c>Hello </c>
        </b>
        <b>
            <c>World!</c>
        </b>
    </a>
    <a1>
        <b>
            <c>No</c>
        </b>
        <b>
            <c>Hello</c>
        </b>
    </a1>
</root>

EOD,
                <<<'EOD'
<?xml version="1.0" encoding="UTF-8"?>
<root>
    <a>
        <b>
            <c>Hello World!</c>
        </b>
        
    </a>
    <a1>
        <b>
            <c>No</c>
        </b>
        <b>
            <c>Hello</c>
        </b>
    </a1>
</root>

EOD,
                ['a', 'b', 'bb', 'c'],
            ],
            'style mismatch' => [
                <<<'EOD'
<?xml version="1.0" encoding="UTF-8"?>
<root>
    <a>
        <b>
            <bb font="bold"/>
            <c>Hello </c>
        </b>
        <b>
            <bb/>
            <c>World</c>
        </b>
        <b>
            <bb/>
            <c>!!!</c>
        </b>
    </a>
</root>

EOD,
                <<<'EOD'
<?xml version="1.0" encoding="UTF-8"?>
<root>
    <a>
        <b>
            <bb font="bold"/>
            <c>Hello </c>
        </b>
        <b>
            <bb/>
            <c>World!!!</c>
        </b>
        
    </a>
</root>

EOD,
                ['a', 'b', 'bb', 'c'],
            ],
            'correct clone' => [
                <<<'EOD'
<?xml version="1.0" encoding="UTF-8"?>
<root>
    <a>
        <b hello="world">
            <bb/>
            <c font="bold">Hello </c>
        </b>
        <b hello="world">
            <bb/>
            <c>World!</c>
        </b>
    </a>
</root>

EOD,
                <<<'EOD'
<?xml version="1.0" encoding="UTF-8"?>
<root>
    <a>
        <b hello="world">
            <bb/>
            <c font="bold">Hello World!</c>
        </b>
        
    </a>
</root>

EOD,
                ['a', 'b', 'bb', 'c'],
            ],
        ];
    }

    /**
     * @param array<string> $paths
     *
     * @dataProvider cleanupProvider
     */
    public function testCleanup(string $document, string $expected, array $paths): void
    {
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->loadXML($document);

        list($paragraphQuery, $runQuery, $propertyQuery, $textQuery) = $paths;
        $cleanup = new Cleanup($doc, $paragraphQuery, $runQuery, $propertyQuery, $textQuery);
        $cleanup->cleanup();

        $this->assertEquals($expected, $doc->saveXML());
    }

    /**
     * @dataProvider
     *
     * @return array<string,mixed>
     */
    public function hardcoreCleanupProvider(): array
    {
        return [
            'strip lang nodes' => [
                <<<'EOD'
<?xml version="1.0" encoding="UTF-8"?>
<root xmlns:w="https://schemas.openxmlformats.org/wordprocessingml/2006/main">
    <w:lang abc="123"/>
    <a>
        <w:lang abc="123"/>
        <b>
            <w:lang abc="123"/>
            <c>Hello </c>
        </b>
        <b>
            <c>World!</c>
        </b>
    </a>
</root>

EOD,
                <<<'EOD'
<?xml version="1.0" encoding="UTF-8"?>
<root xmlns:w="https://schemas.openxmlformats.org/wordprocessingml/2006/main">
    
    <a>
        
        <b>
            
            <c>Hello </c>
        </b>
        <b>
            <c>World!</c>
        </b>
    </a>
</root>

EOD,
                ['a', 'b', 'bb', 'c'],
            ],
            'strip empty run prop nodes' => [
                <<<'EOD'
<?xml version="1.0" encoding="UTF-8"?>
<root xmlns:w="https://schemas.openxmlformats.org/wordprocessingml/2006/main">
    <a>
        <b>
            <w:rPr abc="123"/>
            <c>Hello </c>
        </b>
        <b>
            <w:rPr/>
            <c>World!</c>
        </b>
    </a>
</root>

EOD,
                <<<'EOD'
<?xml version="1.0" encoding="UTF-8"?>
<root xmlns:w="https://schemas.openxmlformats.org/wordprocessingml/2006/main">
    <a>
        <b>
            <w:rPr abc="123"/>
            <c>Hello </c>
        </b>
        <b>
            <w:rPr/>
            <c>World!</c>
        </b>
    </a>
</root>

EOD,
                ['a', 'b', 'bb', 'c'],
            ],
        ];
    }

    /**
     * @param array<string> $paths
     *
     * @dataProvider hardcoreCleanupProvider
     */
    public function testHardcoreCleanup(string $document, string $expected, array $paths): void
    {
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->loadXML($document);

        list($paragraphQuery, $runQuery, $propertyQuery, $textQuery) = $paths;
        $cleanup = new Cleanup($doc, $paragraphQuery, $runQuery, $propertyQuery, $textQuery);
        $cleanup->hardcoreCleanup();

        $this->assertEquals($expected, $doc->saveXML());
    }
}
