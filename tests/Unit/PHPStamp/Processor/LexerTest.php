<?php

namespace PHPStamp\Tests\Unit\PHPStamp\Processor;

use PHPStamp\Processor\Lexer;
use PHPUnit\Framework\TestCase;

class LexerTest extends TestCase
{
    public function testLexer()
    {
        $brackets = array('[[', ']]');
        $lexer = new Lexer($brackets);

        $sampleInput = 'This is a test string with a lot!!! of special character and [[ tag ]] inside.';
        $lexer->setInput($sampleInput);

        $expectedStructure = array(
            Lexer::T_STRING, Lexer::T_STRING, Lexer::T_STRING, Lexer::T_STRING, Lexer::T_STRING,
            Lexer::T_STRING, Lexer::T_STRING, Lexer::T_STRING,
            Lexer::T_NEGATE, Lexer::T_NEGATE, Lexer::T_NEGATE,
            Lexer::T_STRING, Lexer::T_STRING, Lexer::T_STRING, Lexer::T_STRING,
            Lexer::T_OPEN_BRACKET, Lexer::T_STRING, Lexer::T_CLOSE_BRACKET, Lexer::T_STRING, Lexer::T_DOT
        );

        $structure = array();
        while ($fragment = $lexer->peek()) {
            $structure[] = $fragment['type'];
        }

        $this->assertEquals($expectedStructure, $structure);
    }

    public function testCustomBrackets()
    {
        $brackets = array('{% tag %}', '{% endtag %}');
        $lexer = new Lexer($brackets);

        $sampleInput = 'This is a test string with a lot!!! of special character and {% tag %} tag {% endtag %} inside.';
        $lexer->setInput($sampleInput);

        $expectedStructure = array(
            Lexer::T_STRING, Lexer::T_STRING, Lexer::T_STRING, Lexer::T_STRING, Lexer::T_STRING,
            Lexer::T_STRING, Lexer::T_STRING, Lexer::T_STRING,
            Lexer::T_NEGATE, Lexer::T_NEGATE, Lexer::T_NEGATE,
            Lexer::T_STRING, Lexer::T_STRING, Lexer::T_STRING, Lexer::T_STRING,
            Lexer::T_OPEN_BRACKET, Lexer::T_STRING, Lexer::T_CLOSE_BRACKET, Lexer::T_STRING, Lexer::T_DOT
        );

        $structure = array();
        while ($fragment = $lexer->peek()) {
            $structure[] = $fragment['type'];
        }

        $this->assertEquals($expectedStructure, $structure);
    }
}
