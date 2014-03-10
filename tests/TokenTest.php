<?php

namespace tests;

use OfficeML\Processor\Token;

class TokenTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_collect_tokens()
    {
        $document = new \DOMDocument('1.0', 'utf-8');
        $element = $document->createElement('container');

        // 10 - 19
        $token = new Token('[[token]]', 'token', 10, $element);

        // С участком токена
        $isIntersect = $token->intersection(5, 12);
        $this->assertEquals($isIntersect, true);

        // Вообще мимо
        $isIntersect = $token->intersection(1, 5);
        $this->assertEquals($isIntersect, false);

        // Цепляет но без участка справа
        $isIntersect = $token->intersection(5, 10);
        $this->assertEquals($isIntersect, false);

        // Цепляет но без участка слева
        $isIntersect = $token->intersection(19, 20);
        $this->assertEquals($isIntersect, false);
    }
}
 