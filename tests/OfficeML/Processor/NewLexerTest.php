<?php

namespace tests\OfficeML\Processor;

use OfficeML\Processor\NewLexer;

class NewLexerTest extends \PHPUnit_Framework_TestCase
{
    public function testInit()
    {
        $brackets = array('[[', ']]');
        $lexer = new NewLexer($brackets);

        $input = 'Outside text with token [[subject]] in it. Function [[cell(name, students)]] is also presented.';
        $lexer->setInput($input);

        $tokens = array(
            array(
                'value' => 'Outside text with token ',
                'type'  => NewLexer::TYPE_TEXT,
                'position' => 0
            ),
            array(
                'value' => '[[',
                'type'  => NewLexer::TYPE_LEFT_BRACKET,
                'position' => 24
            ),
            array(
                'value' => 'subject',
                'type'  => NewLexer::TYPE_TOKEN_VALUE,
                'position' => 26
            ),
            array(
                'value' => ']]',
                'type'  => NewLexer::TYPE_RIGHT_BRACKET,
                'position' => 33
            ),
            array(
                'value' => ' in it. Function ',
                'type'  => NewLexer::TYPE_TEXT,
                'position' => 35
            ),
            array(
                'value' => '[[',
                'type'  => NewLexer::TYPE_LEFT_BRACKET,
                'position' => 52
            ),
            array(
                'value' => 'cell',
                'type'  => NewLexer::TYPE_TOKEN_VALUE,
                'position' => 54
            ),
            array(
                'value' => 'name',
                'type'  => NewLexer::TYPE_TOKEN_ARGUMENT,
                'position' => 59
            ),
            array(
                'value' => 'students',
                'type'  => NewLexer::TYPE_TOKEN_ARGUMENT,
                'position' => 65
            ),
            array(
                'value' => ']]',
                'type'  => NewLexer::TYPE_RIGHT_BRACKET,
                'position' => 74
            ),
            array(
                'value' => 'is also presented.',
                'type'  => NewLexer::TYPE_TEXT,
                'position' => 76
            )
        );

        foreach ($tokens as $expected) {
            $lexer->moveNext();
            $actual = $lexer->lookahead;
            $this->assertEquals($expected['value'], $actual['value']);
            $this->assertEquals($expected['type'], $actual['type']);
            $this->assertEquals($expected['position'], $actual['position']);
        }

        $this->assertNull($lexer->moveNext());
    }
}
 