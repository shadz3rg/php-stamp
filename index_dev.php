<?php
require 'vendor/autoload.php';
use OfficeML\Helper;
use OfficeML\Document;
use OfficeML\Processor;
use OfficeML\Templator;

Processor::$filters['cell'] = function($arg, array $token, DOMNode $node, DOMDocument $document) {
    //$token['value'] = 'concat("(:", "' . $token['value'] . '", ":)")';
    return $token;
};

$filePath = __DIR__ . '/static/template.docx';
$cachePath = __DIR__ . '/static/cache/';

$templater = Templator::create($filePath, $cachePath);
$templater->debug = true;

$templater->assign(array(
    'username' => 'Няша',
    'usernamex' => 'Няшечка',
    'action' => array(
        'subject' => 'пивка',
        'name' => 'попить'
    ),
    'students' => array(
        array('id' => 1, 'name' => 'Иванов', 'mark' => 5),
        array('id' => 2, 'name' => 'Петров', 'mark' => 3),
        array('id' => 3, 'name' => 'Сидоров', 'mark' => 4)
    ),
    'name' => 'Abc'
));

echo '<pre>';
$res = $templater->output();
//$res->preserveWhiteSpace = true;
//$res->formatOutput = true;
//echo htmlentities($res->saveXML());
//

echo '</pre>';
//$templater->download();

