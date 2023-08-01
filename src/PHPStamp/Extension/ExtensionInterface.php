<?php

namespace PHPStamp\Extension;

use PHPStamp\Processor\Tag;

interface ExtensionInterface
{
    /**
     * Sets tag to work with.
     */
    public function __construct(Tag $tag);

    /**
     * Prepare all the things and apply current extension.
     *
     * @return void
     */
    public function execute(array $arguments, \DOMElement $node);
}
