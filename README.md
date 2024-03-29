PHPStamp
=========

PHPStamp is a simple templating engine for [XML-based Microsoft Word documents](https://learn.microsoft.com/en-us/office/open-xml/word/structure-of-a-wordprocessingml-document?tabs=cs).  
Library aims to provide native XML-way of templating for DOCX documents as an alternative to treating its content as a plain text for regex replacing, which has a lot of downsides.  
Instead it tries to clean messy WYSIWYG-generated code and create reusable XSL stylesheet from document.  

Some additional information:  
(EN) https://redd.it/30conp  
(RU) https://habr.com/ru/articles/244421/  
 
Features
----
  - Caching XSL template to filesystem for fast document render.
  - Track document mode - generate and cache new template if original document was updated.
  - Configurable brackets for placeholder tags.
  - Basic extension system, which helps to generate content blocks such as Cells or ListItems.

Known issues
----
Values inserted into placeholder tags may be highlighted as incorrect by spellcheck, since library removes language attribute and MS Word tries to check it with system language.

Requirements
----
Library requires PHP 7.4+ with DOM, XSL, Zip extensions.

Installation
----
Install with Composer:

`composer require shadz3rg/php-stamp`

Usage
----

##### Template:  

![alt tag](https://habrastorage.org/files/0bf/dbf/f89/0bfdbff896ba45e1ac966c54abd050aa.png)

```php
<?php
    require 'vendor/autoload.php';
    
    use PHPStamp\Templator;
    use PHPStamp\Document\WordDocument;
    
    $cachePath = 'path/to/writable/directory/';
    $templator = new Templator($cachePath);
    
    // Enable debug mode to re-generate template with every render call.
    // $templator->debug = true;
    
    // Enable track mode to generate template with every original document change.
    // $templator->trackDocument = true;
    
    $documentPath = 'path/to/document.docx';
    $document = new WordDocument($documentPath);
    
    $values = [
        'library' => 'PHPStamp 0.1',
        'simpleValue' => 'I am simple value',
        'nested' => [
            'firstValue' => 'First child value',
            'secondValue' => 'Second child value'
        ],
        'header' => 'test of a table row',
        'students' => [
            ['id' => 1, 'name' => 'Student 1', 'mark' => '10'],
            ['id' => 2, 'name' => 'Student 2', 'mark' => '4'],
            ['id' => 3, 'name' => 'Student 3', 'mark' => '7']
        ],
        'maxMark' => 10,
        'todo' => [
            'TODO 1',
            'TODO 2',
            'TODO 3'
        ]
    ];
    $result = $templator->render($document, $values);
    
    // Now you can get template result.
    // 1. HTTP Download
    $result->download();
    
    // Or
    // 2. Save to file
    // $saved = $result->save(__DIR__ . '/static', 'Test_Result1.docx');
    // if ($saved === true) {
    //     echo 'Saved!';
    // }
    
    // Or
    // 3. Buffer output
    // echo $result->output();
```

##### Result:  

![alt tag](https://habrastorage.org/files/290/6aa/6e6/2906aa6e6cba4fa08655b1f58463a4d8.png)
