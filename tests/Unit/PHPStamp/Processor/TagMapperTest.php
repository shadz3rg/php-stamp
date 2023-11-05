<?php

namespace PHPStamp\Tests\Unit\PHPStamp\Processor;

use PHPStamp\Exception\ProcessorException;
use PHPStamp\Processor\Lexer;
use PHPStamp\Processor\Tag;
use PHPStamp\Processor\TagMapper;
use PHPStamp\Tests\BaseCase;

class TagMapperTest extends BaseCase
{
    /**
     * @dataProvider
     *
     * @return array<string,mixed>
     */
    public function parseProvider(): array
    {
        return [
            'non found' => ['hello worlds some content', []],
            'base case' => [
                'hello [[username]]!',
                [
                    new Tag(
                        [
                            'textContent' => '[[username]]',
                            'position' => 6,
                            'length' => 12,
                        ],
                        ['username'],
                        []
                    ),
                ],
            ],
            'nested path' => [
                'hello [[level0.level1.username]]!',
                [
                    new Tag(
                        [
                            'textContent' => '[[level0.level1.username]]',
                            'position' => 6,
                            'length' => 26,
                        ],
                        ['level0', 'level1', 'username'],
                        []
                    ),
                ],
            ],
            'multiple tags' => [
                'hello [[username]]! Welcome on [[planet]]!',
                [
                    new Tag(
                        [
                            'textContent' => '[[username]]',
                            'position' => 6,
                            'length' => 12,
                        ],
                        ['username'],
                        []
                    ),
                    new Tag(
                        [
                            'textContent' => '[[planet]]',
                            'position' => 31,
                            'length' => 10,
                        ],
                        ['planet'],
                        []
                    ),
                ],
            ],
            'has function' => [
                'hello [[username:func(arg1, arg2)]]!',
                [
                    new Tag(
                        [
                            'textContent' => '[[username:func(arg1, arg2)]]',
                            'position' => 6,
                            'length' => 29,
                        ],
                        ['username'],
                        [
                            ['function' => 'func', 'arguments' => ['arg1', 'arg2']],
                        ]
                    ),
                ],
            ],
            'has malformed function' => [
                'hello [[username:]]!',
                null,
                new ProcessorException('Unexpected token, expected PHPStamp\\Processor\\Lexer::T_STRING, got PHPStamp\\Processor\\Lexer::T_CLOSE_BRACKET'),
            ],
            'has malformed function args' => [
                'hello [[username:xxx(]]!',
                null,
                new ProcessorException('Unexpected token, expected PHPStamp\\Processor\\Lexer::T_CLOSE_PARENTHESIS, got PHPStamp\\Processor\\Lexer::T_CLOSE_BRACKET'),
            ],
            'no closing bracket' => [
                'hello [[username:xxx(',
                null,
                new ProcessorException('Cant match closing bracket'),
            ],
        ];
    }

    /**
     * @param array<Tag> $expected
     *
     * @dataProvider parseProvider
     */
    public function testParse(string $content, ?array $expected, ProcessorException $exception = null): void
    {
        if ($exception !== null) {
            $this->expectExceptionObject($exception);
        }

        $lexer = new Lexer(['[[', ']]']);
        $lexer->setInput($content);

        $mapper = new TagMapper();

        $tags = [];
        while ($tag = $mapper->parse($lexer)) {
            $tags[] = $tag;
        }

        $this->assertEquals($expected, $tags);
    }
}
