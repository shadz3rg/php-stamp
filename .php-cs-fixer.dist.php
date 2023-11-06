<?php

$finder = PhpCsFixer\Finder::create()->in('src/');

$config = new PhpCsFixer\Config();
return $config
    ->setRules([
        '@Symfony' => true,
        'array_syntax' => ['syntax' => 'short'],
        'yoda_style' => false,
        'ordered_imports' => ['imports_order' => ['class', 'function', 'const'], 'sort_algorithm' => 'alpha']
    ])
    ->setFinder($finder);
