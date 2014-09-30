<?php
/**
 * Created by PhpStorm.
 * User: shooz
 * Date: 21.09.14
 * Time: 14:52
 */

namespace OfficeML\Processor;


class Tag
{
    /**
     * @var array
     */
    private $summary;
    /**
     * @var array
     */
    private $path;
    /**
     * @var array
     */
    private $functions;

    public function __construct(array $summary, array $path, array $functions)
    {
        $this->summary = $summary;
        $this->path = $path;
        $this->functions = $functions;
    }

    public function getPosition()
    {
        return $this->summary['position'];
    }

    public function getLength()
    {
        return $this->summary['length'];
    }

    public function getXmlPath()
    {
        return implode('/', $this->path);
    }

    public function getTextContent()
    {
        return $this->summary['textContent'];
    }
} 