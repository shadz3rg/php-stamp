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

    public function getTokens($containerQuery)
    {
        $query = sprintf(
            $containerQuery . '[contains(., "%s")][contains(., "%s")]',
            $this->brackets[0],
            $this->brackets[1]
        );

        $containers = $this->xpath->query($query);

        $entries = new TokenCollection();
        foreach($containers as $containerNode) {
            $containerOffset = 0;
            $this->lexer->setInput(utf8_decode($containerNode->textContent));

            while ($token = $this->lexer->next()) {
                $entries->add($this->mapObject($token, $containerNode, $containerOffset));
                $containerOffset += mb_strlen($token['token']); // TODO Сделать оффсет покрасивше
            }
        }

        return $entries;
    }

    private function mapObject(array $token, \DOMNode $container, $containerOffset)
    {
        $tokenObject = new Token($token['token'], $token['value'], $token['position'], $container);
        $tokenObject->setOffset($containerOffset);
        return $tokenObject;
    }
} 