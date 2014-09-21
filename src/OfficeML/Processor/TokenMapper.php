<?php

namespace OfficeML\Processor;

use OfficeML\Exception\ParsingException;

class TokenMapper
{
    public function __construct()
    {
    }

    public function parse(\DOMNodeList $nodeList, Lexer $lexer)
    {
        $tokenCollection = new TokenCollection();

        /** @var \DOMNode $currentNode */
        foreach($nodeList as $currentNode) {
            $lexer->setInput(utf8_decode($currentNode->nodeValue));

            while ($lexer->moveNext()) {

                if ($lexer->isNextToken(Lexer::T_OPEN_BRACKET) === true) {
                    $tag = $this->handleToken($lexer);
                    ProcessorNew::insertTemplateLogic();
                }

                //var_dump($lexer->getLiteral($lexer->lookahead['type']));






                // Add field
                /*if ($field !== null) {
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

                        $tokenSummary[] = $this->mapObject($tokenFields, $currentNode);
                    }
                    $tokenFields = array();
                }*/
            }
        }

        return $tokenCollection;
    }


    private function handleToken(Lexer $lexer)
    {
        $tag = array();
        $tagIsOpened = false;

        do {
            $token = $lexer->lookahead;

            $tag[] = $token['value']; // full tag to replace

            switch ($token['type']) {

                case Lexer::T_OPEN_BRACKET:
                    if ($tagIsOpened === false) {
                        $tagIsOpened = true;
                    } else {
                        throw new ParsingException('Nested token or not closed bracket.');
                    }
                    break;

                case Lexer::T_CLOSE_BRACKET:
                    return implode($tag);

                case Lexer::T_STRING:
                    if (ctype_alnum($token['value']) === false) {
                        //throw new ParsingException('ctype_alnum expected as identifier.');
                    }
                    break;

                case Lexer::T_DOT: // next levels of id
                    //$lexer->moveNext();
                    //return handleString($lexer, 'path');

                case Lexer::T_COLON:
                    //$lexer->moveNext();
                    //return handleFunction($lexer, 'func');
            }

        } while ($lexer->moveNext());


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