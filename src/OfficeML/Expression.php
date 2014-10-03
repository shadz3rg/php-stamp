<?php

namespace OfficeML;

use OfficeML\Processor\Tag;

interface Expression
{
    public function insertTemplateLogic(array $arguments, \DOMNode $node, Tag $tag);
} 