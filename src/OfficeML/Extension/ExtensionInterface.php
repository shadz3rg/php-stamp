<?php

namespace OfficeML\Extension;

use OfficeML\Processor\Tag;

interface ExtensionInterface
{
    /**
     * Sets tag to work with.
     *
     * @param Tag $tag
     */
    function __construct(Tag $tag);

    /**
     * Prepare all the things and apply current extension.
     *
     * @param array $arguments
     * @param \DOMElement $node
     * @return void
     */
    function execute(array $arguments, \DOMElement $node);
} 