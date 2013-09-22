<?php
namespace OfficeML;

class Lexer extends \Doctrine\Common\Lexer\AbstractLexer {
    const T_NONE = 0;
    const T_OPEN_TAG = 1;
    const T_CLOSE_TAG = 2;

    private $validTokenName = '[^\s]*?';

    /**
     * Creates a new query scanner object.
     */
    public function __construct(array $brackets) {
        foreach ($brackets as $bracket) {
            $this->patterns[] = addslashes($bracket);
        }
    }

    /**
     * @inheritdoc
     */
    protected function getCatchablePatterns() {
        return array('\[\[[^\s]*?\]\]');
    }

    /**
     * @inheritdoc
     */
    protected function getNonCatchablePatterns() {
        return array('.');
    }

    /**
     * @inheritdoc
     */
    protected function getType(&$value) {
        $type = self::T_NONE;

        if ($value === '[[') {
            $type = self::T_OPEN_TAG;
        } elseif ($value === ']]') {
            $type = self::T_CLOSE_TAG;
        }

        return $type;
    }
}