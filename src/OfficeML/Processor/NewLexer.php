<?php
namespace OfficeML\Processor;

use Doctrine\Common\Lexer\AbstractLexer;

class NewLexer extends AbstractLexer
{
    const TYPE_TEXT = 1;
    const TYPE_LEFT_BRACKET = 2;
    const TYPE_TOKEN_VALUE = 3;
    const TYPE_TOKEN_FUNCTION = 4;
    const TYPE_TOKEN_ARGUMENT = 5;
    const TYPE_RIGHT_BRACKET = 6;

    private $bracketsQuery = '';

    public function __construct(array $brackets)
    {
        $toQuery = array(
            '(?:' . preg_quote($brackets[0]) . ')',
            '(?:' . preg_quote($brackets[1]) . ')'
        );

        $this->bracketsQuery = implode($toQuery);
    }

    /**
     * Lexical catchable patterns.
     *
     * @return array
     */
    protected function getCatchablePatterns()
    {
        return array();
    }

    /**
     * Lexical non-catchable patterns.
     *
     * @return array
     */
    protected function getNonCatchablePatterns()
    {
        // TODO: Implement getNonCatchablePatterns() method.
    }

    /**
     * Retrieve token type. Also processes the token value if necessary.
     *
     * @param string $value
     *
     * @return integer
     */
    protected function getType(&$value)
    {
        // TODO: Implement getType() method.
    }
}