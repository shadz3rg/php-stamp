PHPStamp
=========

PHPStamp is a *prototype* of a simple PHP templating library for XML-based Office documents.  
Aim of this software is to provide native XML way of templating this formats as an altenative of treating DOM document as string for regex replacing, which has a lot of downsides.  
Basically this library tries to clean messy WYSIWYG-generated code and create reusable XSL stylesheet from it.  

Features
----
  - Current version supports only Microsoft Office Open XML .docx format. Odt support WIP.
  - Configurable brackets for tags-placeholders.
  - Basic extension system which helps generating block of content such as Cells or ListItems.
  - Caching XSL template to filesystem.

Requirements
----
Library requires PHP 5.3+ with DOM, XSL and Zip extensions.  
Also depends on ```doctrine2/Lexer``` Composer package.

Installation
----
Just install it through composer.
```json
{
    "require": {
       "shadz3rg/PHPStamp": "dev-master"
    }
}
```

Usage
----
```php
<?php
    require 'vendor/autoload.php';
    
    use PHPStamp\Templator;
    use PHPStamp\Document\WordDocument;
    
    $cachePath = 'path/to/writable/directory/';
    $optionalBrackets = array('(((', ')))');
    $templator = new Templator($cachePath, $optionalBrackets);
    
    $documentPath = 'path/to/document.docx';
    $document = new WordDocument($documentPath);
    
    $values = array(
        'tag' => 'value', 
        'row' => array(
            'tag1' => 'value1', 
            'tag2' => 'value2'
        )
    );
    $result = $templator->render($document, $values);
    $result->download();
```

Version
----

Still just experement :3

