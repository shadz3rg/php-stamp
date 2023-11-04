<?php

namespace PHPStamp\Tests\Unit\PHPStamp\Core;

use PHPStamp\Core\CommentTransformer;
use PHPStamp\Exception\DecodeException;
use PHPStamp\Exception\EncodeException;
use PHPStamp\Tests\BaseCase;

class CommentTransformerTest extends BaseCase
{
    public function setUp(): void
    {
        $this->transormer = new CommentTransformer();
    }

    /**
     * @dataProvider
     * @return array
     */
    public function transformProvider(): array
    {
        return [
            'base case' => [['test' => 123, 'test_2' => 321], '{"test":123,"test_2":321}', null],
            'empty payload' => [[], '{}', null],
            'malformed payload' => [['test' => "\xB1\x31"], null, EncodeException::class],
        ];
    }

    /**
     * @dataProvider transformProvider
     * @throws EncodeException
     */
    public function testTransform(array $payload, ?string $expected = null, ?string $exception = null): void
    {
        if ($exception !== null) {
            $this->expectException($exception);
        }

        $result = $this->transormer->transform($payload);
        if ($expected !== null) {
            $this->assertEquals($expected, $result);
        }
    }

    /**
     * @dataProvider
     * @return array
     */
    public function reverseTransformProvider(): array
    {
        return [
            'base case' => ['{"test":123,"test_2":321}', ['test' => 123, 'test_2' => 321],  null],
            'empty payload' => ['{}', [], null],
            'malformed payload' => ['{"malformed payload"}', null, DecodeException::class],
        ];
    }

    /**
     * @dataProvider reverseTransformProvider
     * @throws DecodeException
     */
    public function testReverseTransform(string $serialized, ?array $expected = null, ?string $exception = null): void
    {
        if ($exception !== null) {
            $this->expectException($exception);
        }

        $result = $this->transormer->reverseTransformer($serialized);
        if ($expected !== null) {
            $this->assertEquals($expected, $result);
        }
    }
}
