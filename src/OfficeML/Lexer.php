<?php
namespace OfficeML;

class Lexer {
    private $tokens;
    private $position;
    private $pattern;
    private $validTokenName = '([^\s]*?)';

    public function __construct(array $brackets) {
        $brackets = array_map(function($bracket){
                return '(?:' . preg_quote($bracket) . ')';
            }, $brackets);
        $this->pattern = implode($this->validTokenName, $brackets);
    }

    public function setInput($input)
    {
        $this->tokens = array();
        $this->position = 0;
        $this->scan($input);
    }

    protected function scan($input)
    {
        static $regex;

        if (!isset($regex)) {
            $regex = '/' . $this->pattern . '/ui';
        }

        preg_match_all($regex, $input, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);

        foreach ($matches as $match) {
            $this->tokens[] = array(
                'token' => $match[0][0],
                'value' => str_replace('.', '/', $match[1][0]),
                'position' => array(
                    $match[0][1],
                    $match[0][1] + mb_strlen($match[0][0])
                )
            );
        }
    }

    public function next()
    {
        $token = false;
        if (isset($this->tokens[$this->position])) {
            $token = $this->tokens[$this->position++];
        }
        return $token;
    }
}