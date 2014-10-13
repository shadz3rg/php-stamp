<?php

namespace PHPStamp\Processor;

use PHPStamp\Exception\ParsingException;
use PHPStamp\Exception\ProcessorException;

class TagMapper
{
    public function parse(Lexer $lexer)
    {
        while ($lexer->moveNext()) {
            if ($lexer->isNextToken(Lexer::T_OPEN_BRACKET) === true) {
                $tagData = $this->parseTag($lexer);
                return $this->mapObject($tagData);
            }
        }

        return null;
    }

    private function parseTag(Lexer $lexer)
    {
        // Defaults
        $tagData = array(
            'summary' => array(
                'textContent' => '',
                'position' => $lexer->lookahead['position'], // currently on Lexer::T_OPEN_BRACKET
                'length' => 0
            ),
            'path' => array(),
            'functions' => array()
        );

        // *required Parsed path
        $tagData['path'] = $this->parsePath($lexer);

        // *optional Parsed functions
        while ($lexer->isNextToken(Lexer::T_COLON)) { // if parsePath stopped on delimiter
            $tagData['functions'][] = $this->parseFunction($lexer);
        }

        // *required End of tag
        $expected = Lexer::T_CLOSE_BRACKET;
        if ($lexer->isNextToken($expected) === false) {
            throw new ParsingException(
                'Unexpected token' .
                ', expected ' . $lexer->getLiteral($expected) .
                ', got ' . $lexer->getLiteral($lexer->lookahead['type'])
            );
        }

        $endAt = $lexer->lookahead['position'] + mb_strlen($lexer->lookahead['value']);
        $tagData['summary']['length'] = $endAt - $tagData['summary']['position'];

        $tagData['summary']['textContent'] = $lexer->getInputBetweenPosition(
            $tagData['summary']['position'],
            $tagData['summary']['length']
        );

        return $tagData;
    }

    private function parsePath(Lexer $lexer, $delimiter = Lexer::T_COLON, $return = Lexer::T_CLOSE_BRACKET)
    {
        $path = array();
        $expected = Lexer::T_STRING;

        while ($lexer->moveNext()) {
            $token = $lexer->lookahead;

            if ($token['type'] === $delimiter || $token['type'] === $return) {
                return $path;
            }

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
            }

        };

        return $path; // IDE fix
    }

    private function parseFunction(Lexer $lexer, $delimiter = Lexer::T_COLON, $return = Lexer::T_CLOSE_BRACKET) {
        $function = null;
        $arguments = array();

        $expected = Lexer::T_STRING;
        $optional = null;

        while ($lexer->moveNext()) {
            $token = $lexer->lookahead;

            if ($token['type'] === $delimiter || $token['type'] === $return) {
                return array('function' => $function, 'arguments' => $arguments);
            }

            if ($token['type'] !== $expected && $token['type'] !== $optional) { var_dump($token);
                throw new ProcessorException(
                    'Unexpected token' .
                    ', expected ' . $lexer->getLiteral($expected) .
                    ', got ' . $lexer->getLiteral($token['type'])
                );
            }

            $optional = null; // Reset as we passed through

            switch ($token['type']) {

                case Lexer::T_STRING:
                    // Function id
                    if ($function === null) {
                        $function = $token['value'];

                        $expected = Lexer::T_OPEN_PARENTHESIS;
                        $optional = null;

                        break;
                    }

                    // Fall for arguments parsing
                    $arguments[] = $token['value'];

                    $expected = Lexer::T_CLOSE_PARENTHESIS;
                    $optional = Lexer::T_COMMA;

                    break;

                case Lexer::T_COMMA:
                    $expected = Lexer::T_STRING;

                    break;

                case Lexer::T_OPEN_PARENTHESIS:
                    $expected = Lexer::T_CLOSE_PARENTHESIS;
                    $optional = Lexer::T_STRING;

                    break;

                case Lexer::T_CLOSE_PARENTHESIS:
                    $expected = $return;
                    $optional = $delimiter;

                    break;
            }

        };

        return array('function' => $function, 'arguments' => $arguments);  // IDE fix
    }

    private function mapObject(array $tagData)
    {
        return new Tag($tagData['summary'], $tagData['path'], $tagData['functions']);
    }
} 