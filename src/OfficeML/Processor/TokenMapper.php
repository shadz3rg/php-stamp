<?php

namespace OfficeML\Processor;

class TokenMapper
{
    private $lexer;
    private $xpath;
    private $content;
    private $brackets;

    public function __construct(\DOMDocument $content, $brackets)
    {
        $this->content = $content;
        $this->brackets = $brackets;

        $this->lexer = new Lexer($this->brackets);
        $this->xpath = new \DOMXPath($this->content);
    }

    public function parseForTokens($textQuery)
    {
        // query for text nodes with strict path //w:p/w:r/w:t for tokens
        $query = sprintf(
            $textQuery . '[contains(., "%s")][contains(., "%s")]',
            $this->brackets[0],
            $this->brackets[1]
        );
        $textNodeList = $this->xpath->query($query);

        $tokens = new TokenCollection();
        foreach($textNodeList as $textNode) {
            $this->lexer->setInput(utf8_decode($textNode->textContent));

            // code here

        }

        return $tokens;
    }

    private function mapObject(array $token, \DOMNode $container)
    {
        $tokenObject = new Token($token['token'], $token['value'], $token['position'], $container);
        return $tokenObject;
    }
} 