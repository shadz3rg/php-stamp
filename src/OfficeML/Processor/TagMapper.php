<?php

namespace OfficeML\Processor;

use OfficeML\Exception\ParsingException;
use OfficeML\Exception\ProcessorException;
use OfficeML\Processor\Tag;

class TagMapper
{
    public function parse(Lexer $lexer)
    {
        while ($lexer->moveNext()) {
            if ($lexer->isNextToken(Lexer::T_OPEN_BRACKET) === true) {
                $lexer->moveNext(); // step on T_OPEN_BRACKET

                $tagData = $this->parseTag($lexer);
                return $this->mapObject($tagData);
            }
        }

        return null;
    }

    private function parseTag(Lexer $lexer)
    {
        $openBracketToken = $lexer->token;

        $tagData = array(
            'summary' => array(
                'position' => $openBracketToken['position'],
                'length' => 0
            ),
            'path' => array(),
            'functions' => array()
        );
var_dump($lexer);
        $tagData['path'] = $this->parsePath($lexer);







/*
        do {
            $token = $lexer->token;

            switch ($token['type']) {

                case Lexer::T_OPEN_BRACKET:
                    if ($tagIsOpened === false) {
                        $tagIsOpened = true;
                        $tagData['summary']['position'] = $token['position'];
                        $tagData['path'] = $this->parsePath($lexer);
                    } else {
                        throw new ParsingException('Nested token or not closed bracket.');
                    }
                    break;

                case Lexer::T_CLOSE_BRACKET:
                    $endedAt = $token['position'] + strlen($token['value']);
                    $tagData['summary']['length'] = $endedAt - $tagData['summary']['position'];
                    return $tagData;

                case Lexer::T_COLON:
                    //$lexer->moveNext();
                    //return handleFunction($lexer, 'func');
            }

        } while ($lexer->moveNext());*/
    }

    private function parsePath(Lexer $lexer, $delimiter = Lexer::T_COLON)
    {
        $path = array();
        $expected = Lexer::T_STRING;

        while ($token = $lexer->peek()) {

            if ($token['type'] !== $expected) {
                throw new ProcessorException(
                    'Unexpected token' .
                    ', expected ' . $lexer->getLiteral($expected) .
                    ', got ' . $lexer->getLiteral($token['type'])
                );
            }

            switch ($token['type']) {
                case Lexer::T_STRING:
                    $expected = Lexer::T_DOT;
                    $path[] = $token['value']; // TODO cname_alphanum
                    break;

                case Lexer::T_DOT:
                    $expected = Lexer::T_STRING;
                    break;

                case $delimiter:
                    return $path;
            }

        };

        return $path;
    }

    private function mapObject(array $tagData)
    {
        return new Tag($tagData['summary'], $tagData['path'], $tagData['functions']);
    }
} 