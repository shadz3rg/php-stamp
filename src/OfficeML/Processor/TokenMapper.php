<?php

namespace OfficeML\Processor;

use OfficeML\Exception\ParsingException;

class TokenMapper
{
    private $xpath;
    private $content;
    private $brackets;

    public function __construct(\DOMDocument $content, $brackets)
    {
        $this->content = $content;
        $this->brackets = $brackets;
        $this->xpath = new \DOMXPath($this->content);
    }

    public function parseForTokens($textQuery)
    {
        $tokens = new TokenCollection();
        $lexer = new Lexer($this->brackets);

        // query for text nodes (with strict path //w:p/w:r/w:t) containing placeholders
        $query = sprintf(
            $textQuery . '[contains(., "%s")][contains(., "%s")]',
            $this->brackets[0],
            $this->brackets[1]
        );
        $textNodeList = $this->xpath->query($query);

        foreach($textNodeList as $textNode) {
            $lexer->setInput(utf8_decode($textNode->textContent));

            // Globals
            $isTokenOpened = false;
            $tokenFields = array();

            while ($lexer->moveNext()) {
                $field = $this->handleToken($lexer, $isTokenOpened);

                // Add field
                if ($field !== null) {
                    $tokenFields = array_merge_recursive($tokenFields, $field);
                }

                // Token was closed
                if ($isTokenOpened === false) {
                    if (count($tokenFields) !== 0) {
                        $defaults = array(
                            'token' => null,
                            'path' => array(),
                            'func' => null
                        );

                        $tokenFields = array_merge($tokenFields, $defaults);
                        // as in text TODO check whitespaces
                        $tokenFields['token'] = array_splice($this->brackets, 1, 0, implode('.', $tokenFields['path']));

                        $tokenSummary[] = $this->mapObject($tokenFields, $textNode);
                    }
                    $tokenFields = array();
                }
            }
        }

        return $tokens;
    }

    private function handleToken(Lexer $lexer, &$isTokenOpened, $fieldName = null) {
        $token = $lexer->token; // todo look ahead?

        // Validation
        if ($token['type'] === Lexer::T_OPEN_BRACKET && $isTokenOpened === true) {
            throw new ParsingException('Nested token or not closed bracket.');
        }

        // Close / Open token
        switch ($token['type']) {
            case Lexer::T_OPEN_BRACKET:
                $isTokenOpened = true;
                return null;

            case Lexer::T_CLOSE_BRACKET:
                $isTokenOpened = false;
                return null;
        }

        if ($isTokenOpened === true) {
            switch ($token['type']) {

                case Lexer::T_STRING: // first level of id, after opened token only
                    return handleString($lexer, 'path');

                case Lexer::T_DOT: // next levels of id
                    $lexer->moveNext();
                    return handleString($lexer, 'path');

                case Lexer::T_COLON:
                    $lexer->moveNext();
                    return handleFunction($lexer, 'func');
            }
        }
    }

    //
    private function handleString(Lexer $lexer, $expectedField) {
        $token = $lexer->token;

        $lexer->expect(Lexer::T_STRING);

        return array($expectedField => trim($lexer->token['value'])); // trim!
    }

    // todo refactor me
    private function handleFunction(Lexer $lexer, $expectedField) {
        $token = $lexer->token;

        $function = array(
            'id' => null,
            'arg' => array()
        );

        // id
        $lexer->expect(Lexer::T_STRING);
        $function['id'] = trim($token['value']);

        // args
        $lexer->moveNext();
        $token = $lexer->token;
        $lexer->expect(Lexer::T_OPEN_PARENTHESIS);

        while ($lexer->moveNext() && $lexer->token['type'] !== Lexer::T_CLOSE_PARENTHESIS) {
            if ($lexer->token['type'] === Lexer::T_STRING) {
                $function['arg'][] = trim($lexer->token['value']); // trim!
            }
        }

        return array($expectedField => $function);
    }

    private function mapObject(array $token, \DOMNode $container)
    {
        return new Token($token['token'], $token['path'], $token['func'], $container);
    }
} 