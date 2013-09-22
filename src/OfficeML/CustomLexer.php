<?php
namespace OfficeML;

class CustomLexer {
    private $brackets;

    private $tokens;
    private $peek;
    private $position = 0;

    public $token;

    public function __construct(array $brackets) {
        $this->brackets = $brackets;
    }

    public function setInput($input)
    {
        $this->tokens = array();
        $this->reset();
        $this->scan($input);
    }

    public function reset()
    {
        //$this->lookahead = null;
        $this->token = null;
        $this->peek = 0;
        $this->position = 0;
    }

    protected function scan($input)
    {
        static $regex;

        if ( ! isset($regex)) {
            $regex = '/(' . implode(')|(', $this->getCatchablePatterns()) . ')|'
                . implode('|', $this->getNonCatchablePatterns()) . '/ui';

            $regex = '/\[\[[^\s]*?\]\]/ui';
        }

        $flags = PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_OFFSET_CAPTURE;
        $matches = preg_split($regex, $input, -1, $flags);

        foreach ($matches as $match) {
            // Must remain before 'value' assignment since it can change content
            $type = $this->getType($match[0]);

            $this->tokens[] = array(
                'value' => $match[0],
                'type'  => $type,
                'position' => $match[1],
            );
        }
    }

    public function peek()
    {
        if (isset($this->tokens[$this->position + $this->peek])) {
            return $this->tokens[$this->position + $this->peek++];
        } else {
            return null;
        }
    }
}