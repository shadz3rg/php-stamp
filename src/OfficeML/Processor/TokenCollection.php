<?php
namespace OfficeML\Processor;

class TokenCollection implements \Iterator
{
    protected $_position = 0;
    protected $_container = array();

    public function __construct()
    {
        $this->_position = 0;
    }

    public function add(Token $token)
    {
        $this->_container[] = $token;
    }

    public function count()
    {
        return count($this->_container);
    }

    public function rewind()
    {
        $this->_position = 0;
    }

    public function current()
    {
        return $this->_container[$this->_position];
    }

    public function key()
    {
        return $this->_position;
    }

    public function next()
    {
        ++$this->_position;
    }

    public function valid()
    {
        return isset($this->_container[$this->_position]);
    }
}