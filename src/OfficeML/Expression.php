<?php

namespace OfficeML;

interface Expression
{
    public function insertTemplateLogic(array $arguments, \DOMNode $node, \DOMDocument $template);
} 