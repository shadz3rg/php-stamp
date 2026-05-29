<?php

namespace PHPStamp\Tests\Performance\PHPStamp;

use PHPStamp\Tests\BaseCase;
use PHPStamp\XMLHelper;

class XMLHelperPerformanceTest extends BaseCase
{
    public function testXmlEncodeLargeFlatArrayCompletesWithinReasonableTime(): void
    {
        $data = $this->makeFlatData(100000);
        $document = new \DOMDocument('1.0', 'UTF-8');
        $root = $document->createElement('root');
        $document->appendChild($root);

        $elapsed = $this->measure(static function () use ($data, $root, $document): void {
            XMLHelper::xmlEncode($data, $root, $document);
        });

        $this->assertSame(10000, $root->childNodes->length);
        $this->assertLessThan(1.0, $elapsed, sprintf('Encoding 10k flat values took %.4f seconds.', $elapsed));
    }

    public function testXmlEncodeElementCreationOverheadStaysBounded(): void
    {
        $data = $this->makeFlatData(100000);

        $baselineDocument = new \DOMDocument('1.0', 'UTF-8');
        $baselineRoot = $baselineDocument->createElement('root');
        $baselineDocument->appendChild($baselineRoot);
        $baselineElapsed = $this->measure(static function () use ($data, $baselineRoot, $baselineDocument): void {
            foreach ($data as $key => $value) {
                $node = $baselineDocument->createElement($key);
                $baselineRoot->appendChild($node);
                $node->appendChild($baselineDocument->createTextNode($value));
            }
        });

        $document = new \DOMDocument('1.0', 'UTF-8');
        $root = $document->createElement('root');
        $document->appendChild($root);
        $elapsed = $this->measure(static function () use ($data, $root, $document): void {
            XMLHelper::xmlEncode($data, $root, $document);
        });

        $this->assertSame(10000, $root->childNodes->length);
        $this->assertLessThan(
            max(0.1, $baselineElapsed * 20),
            $elapsed,
            sprintf('XMLHelper took %.4fs; direct DOM baseline took %.4fs.', $elapsed, $baselineElapsed)
        );
    }

    /**
     * @return array<string,string>
     */
    private function makeFlatData(int $count): array
    {
        $data = [];
        for ($i = 0; $i < $count; ++$i) {
            $data['key_'.$i] = 'value_'.$i;
        }

        return $data;
    }

    /**
     * @param callable(): void $callback
     */
    private function measure(callable $callback): float
    {
        $start = microtime(true);
        $callback();

        return microtime(true) - $start;
    }
}
