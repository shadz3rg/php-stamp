<?php

namespace tests\OfficeML\Processor;

use OfficeML\Processor\Lexer;

class NewLexerTest extends \PHPUnit_Framework_TestCase
{
    public function testInit()
    {
        $brackets = array('[[', ']]');
        $lexer = new Lexer($brackets);

        $input = 'Outside, a text with token [[subject]] in it. Function1 [[cell(name, students)]] is also presented.';
        $lexer->setInput($input);

        while ($lexer->moveNext()) {
            $token = $lexer->lookahead;
            $token['type'] = $lexer->getLiteral($token['type']);
            var_dump($token);
        }
    }
}
 