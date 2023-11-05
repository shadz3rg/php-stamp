<?php

namespace PHPStamp\Extension;

use PHPStamp\Processor\Tag;

abstract class Extension implements ExtensionInterface
{
    protected Tag $tag;

    /**
     * @inherit
     */
    public function __construct(Tag $tag)
    {
        $this->tag = $tag;
    }

    /**
     * @inherit
     */
    public function execute(array $arguments, \DOMElement $node)
    {
        $arguments = $this->prepareArguments($arguments);
        $this->insertTemplateLogic($arguments, $node);
    }

    /**
     * Prepare / validate / merge with defaults / modify given arguments.
     *
     * @param array<string> $arguments
     *
     * @return array<string>
     */
    abstract protected function prepareArguments(array $arguments);

    /**
     * All template modification magic is here.
     *
     * @param array<string> $arguments
     *
     * @return void
     */
    abstract protected function insertTemplateLogic(array $arguments, \DOMElement $node);
}
