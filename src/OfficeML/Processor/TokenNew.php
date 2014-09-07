<?php

namespace OfficeML\Processor;

class TokenNew
{
    const LEFT = 0;
    const RIGHT = 1;

    private $token;
    private $value;
    private $func;
    private $position;
    private $solved = false;

    private $containerNode;

    /**
     * @param $token string Token w/ brackets.
     * @param $value string Value of token inside brackets.
     * @param $position int Position where token started.
     * @param $containerNode
     */
    public function __construct($token, $value, $position, \DOMNode $containerNode)
    {
        $this->token = $token;
        $this->position = $position;

        // Filters [[students>id:cell]]
        $filter = explode(':', $value);
        if (count($filter) === 2) {
            $this->func = array(
                'name' => $filter[1],
                'arg' => null // TODO Filter arguments
            );
            $this->value = str_replace('.', '/', $filter[0]);
            //todo multiple arguments
        }

        if ($this->value === null) {
            $this->value = str_replace('.', '/', $value);
        }

        $this->containerNode = $containerNode;
    }

    /**
     * Offset from previously stripped characters.
     * @param $offset int
     */
    public function setOffset($offset)
    {
        $this->position -= $offset;
    }

    /**
     * Token getter.
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Value getter.
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Func getter.
     * @return array [name, arg]
     */
    public function getFunc()
    {
        return $this->func;
    }

    public function getContainerNode()
    {
        return $this->containerNode;
    }

    /**
     * Position getter.
     * @return array [left, right]
     */
    public function getPosition()
    {
        return array(
            self::LEFT => $this->position,
            self::RIGHT => $this->position + mb_strlen($this->token)
        );
    }

    /**
     * Token replaced by XSL logic.
     * @return bool
     */
    public function resolve()
    {
        return $this->solved = true;
    }

    /**
     * @return bool
     */
    public function isSolved()
    {
        return $this->solved;
    }

    /**
     * Token length getter.
     * @return int
     */
    public function getLength()
    {
        return mb_strlen($this->token);
    }

    /**
     * Is given position between token left - token right.
     * @param $position
     * @return bool
     */
    public function isInclude($position)
    {
        $tokenPosition = $this->getPosition();

        return ($tokenPosition[self::LEFT] <= $position && $position <= $tokenPosition[self::RIGHT]);
    }

    public function intersection($nodePositionLeft, $nodePositionRight)
    {
        $tokenPosition = $this->getPosition();

        if ($tokenPosition[self::LEFT] <= $nodePositionLeft) {
            return $tokenPosition[self::RIGHT] > $nodePositionLeft;
        }

        return $tokenPosition[self::LEFT] < $nodePositionRight;
    }
} 