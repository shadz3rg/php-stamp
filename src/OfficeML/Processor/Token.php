<?php

namespace OfficeML\Processor;

class Token
{
    private $token;
    private $path;
    private $func;
    private $containerNode;

    /**
     * @param $token string Token w/ brackets.
     * @param $path array Value of token inside brackets.
     * @param $func array|null Position where token started.
     * @param $containerNode
     */
    public function __construct($token, array $path, $func, $containerNode)
    {
        $this->token = $token;
        $this->path = $path;
        $this->func = $func;
        $this->containerNode = $containerNode;
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
     * Path getter.
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     *
     * @return bool
     */
    public function hasFunc()
    {
        return ($this->func !== null);
    }

    /**
     * Func getter.
     * @return array [id, arg]
     */
    public function getFunc()
    {
        return $this->func;
    }

    /**
     * Container getter.
     * @return \DOMNode
     */
    public function getContainerNode()
    {
        return $this->containerNode;
    }
}