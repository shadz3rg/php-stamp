<?php

namespace tests\PHPStamp\Processor;

use PHPStamp\Processor\Lexer;
use PHPUnit\Framework\TestCase;

class LexerTest extends TestCase
{
    public function testLexer()
    {
        $brackets = array('[[', ']]');
        $lexer = new Lexer($brackets);

        $sampleInput = 'This is test string with a lot!!! of special character and [[ tag ]] inside.';
        $lexer->setInput($sampleInput);

        while ($fragment = $lexer->peek()) {
            var_dump($fragment);
        }
    }
}
