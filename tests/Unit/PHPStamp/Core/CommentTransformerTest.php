<?php

namespace PHPStamp\Tests\Unit\PHPStamp\Core;

use PHPStamp\Core\CommentTransformer;
use PHPStamp\Exception\DecodeException;
use PHPStamp\Exception\EncodeException;
use PHPStamp\Tests\BaseCase;
use Throwable;

class CommentTransformerTest extends BaseCase
{
    private CommentTransformer $transformer;

    public function setUp(): void
    {
        $this->transformer = new CommentTransformer();
    }

    /**
     * @dataProvider
     *
     * @return array<string,mixed>
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
     * @param array<string,mixed> $payload
     *
     * @phpstan-param class-string<Throwable> $exception
     *
     * @dataProvider transformProvider
     *
     * @throws EncodeException
     */
    public function testTransform(array $payload, string $expected = null, string $exception = null): void
    {
        if ($exception !== null) {
            $this->expectException($exception);
        }

        $result = $this->transformer->transform($payload);
        if ($expected !== null) {
            $this->assertEquals($expected, $result);
        }
    }

    /**
     * @dataProvider
     *
     * @return array<string,mixed>
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
     * @param array<string,mixed>|null $expected
     *
     * @phpstan-param class-string<Throwable> $exception
     *
     * @dataProvider reverseTransformProvider
     *
     * @throws DecodeException
     */
    public function testReverseTransform(string $serialized, array $expected = null, string $exception = null): void
    {
        if ($exception !== null) {
            $this->expectException($exception);
        }

        $result = $this->transformer->reverseTransformer($serialized);
        if ($expected !== null) {
            $this->assertEquals($expected, $result);
        }
    }
}
