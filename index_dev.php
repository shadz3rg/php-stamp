<?php
require 'vendor/autoload.php';

use OfficeML\Templator;

$filePath = __DIR__ . '/static/template.docx';
$cachePath = __DIR__ . '/static/cache/';

$templator = Templator::create($filePath, $cachePath);
$templator->debug = true;

$templator->assign(array(
    'username' => 'Няша',
    'action' => array(
        'subject' => 'пивка',
        'name' => 'попить'
    ),
    'students' => array(
        array('id' => 1, 'name' => 'Иванов', 'mark' => 5),
        array('id' => 2, 'name' => 'Петров', 'mark' => 3),
        array('id' => 3, 'name' => 'Сидоров', 'mark' => 4)
    )
));
/*
echo '<pre>';
$res = $templator->output();
$res->preserveWhiteSpace = true;
$res->formatOutput = true;
echo htmlentities($res->saveXML());
echo '</pre>';*/
$templator->download();

