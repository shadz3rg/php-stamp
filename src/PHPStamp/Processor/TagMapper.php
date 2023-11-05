<?php

namespace PHPStamp\Processor;

use PHPStamp\Exception\ParsingException;
use PHPStamp\Exception\ProcessorException;

class TagMapper
{
    /**
     * @throws ParsingException
     */
    public function parse(Lexer $lexer): ?Tag
    {
        while ($lexer->moveNext()) {
            if ($lexer->isNextToken(Lexer::T_OPEN_BRACKET) === true) {
                $tagData = $this->parseTag($lexer);

                return $this->mapObject($tagData);
            }
        }

        return null;
    }

    /**
     * @return array{summary: array{position: int, length: int, textContent: string}, path: array<string>, functions: array<array{function: string, arguments: string[]}>}
     *
     * @throws ParsingException
     * @throws ProcessorException
     */
    private function parseTag(Lexer $lexer): array
    {
        /** @var array{type: int, value: string, position: int} $token */
        $token = $lexer->lookahead;

        // Defaults
        $tagData = [
            'summary' => [
                'textContent' => '',
                'position' => $token['position'], // currently on Lexer::T_OPEN_BRACKET
                'length' => 0,
            ],
            'functions' => [],
        ];

        // *required Parsed path
        $tagData['path'] = $this->parsePath($lexer);

        // *optional Parsed functions
        while ($lexer->isNextToken(Lexer::T_COLON)) { // if parsePath stopped on delimiter
            $tagData['functions'][] = $this->parseFunction($lexer);
        }

        // *required End of tag
        $expected = Lexer::T_CLOSE_BRACKET;
        if ($lexer->isNextToken($expected) === false) {
            /** @var array{type: int, value: string, position: int} $token */
            $token = $lexer->lookahead;
            throw new ParsingException('Unexpected token, expected '.$lexer->getLiteral($expected).', got '.$lexer->getLiteral($token['type']));
        }

        /** @var array{type: int, value: string, position: int} $token */
        $token = $lexer->lookahead;
        $endAt = $token['position'] + mb_strlen($token['value']);
        $tagData['summary']['length'] = $endAt - $tagData['summary']['position'];

        $tagData['summary']['textContent'] = $lexer->getInputBetweenPosition(
            $tagData['summary']['position'],
            $tagData['summary']['length']
        );

        return $tagData;
    }

    /**
     * @return array<string>
     *
     * @throws ProcessorException
     */
    private function parsePath(Lexer $lexer, int $delimiter = Lexer::T_COLON, int $return = Lexer::T_CLOSE_BRACKET): array
    {
        $path = [];
        $expected = Lexer::T_STRING;

        while ($lexer->moveNext()) {
            /** @var array{type: int, value: string, position: int} $token */
            $token = $lexer->lookahead;

            if ($token['type'] === $delimiter || $token['type'] === $return) {
                return $path;
            }

            if ($token['type'] !== $expected) {
                throw new ProcessorException('Unexpected token, expected '.$lexer->getLiteral($expected).', got '.$lexer->getLiteral($token['type']));
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
        }

        throw new ProcessorException('Cant match closing bracket');
    }

    /**
     * @return array{function: string, arguments: string[]}
     *
     * @throws ProcessorException
     */
    private function parseFunction(Lexer $lexer, int $delimiter = Lexer::T_COLON, int $return = Lexer::T_CLOSE_BRACKET): array
    {
        $function = null;
        $arguments = [];

        $expected = Lexer::T_STRING;
        $optional = null;

        while ($lexer->moveNext()) {
            /** @var array{type: int, value: string, position: int} $token */
            $token = $lexer->lookahead;

            if ($token['type'] !== $expected && $token['type'] !== $optional) {
                throw new ProcessorException('Unexpected token, expected '.$lexer->getLiteral($expected).', got '.$lexer->getLiteral($token['type']));
            }

            if ($function !== null && ($token['type'] === $delimiter || $token['type'] === $return)) {
                return ['function' => $function, 'arguments' => $arguments];
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
        }

        throw new ProcessorException('Cant match closing bracket');
    }

    /**
     * @param array{summary: array{position: int, length: int, textContent: string}, path: array<string>, functions: array<array{function: string, arguments: string[]}>} $tagData
     */
    private function mapObject(array $tagData): Tag
    {
        return new Tag($tagData['summary'], $tagData['path'], $tagData['functions']);
    }
}
