PHPStamp
=========

PHPStamp is a *prototype* of simple PHP templating library for XML-based office file documents.  
This library tries to simplify messy WYSIWYG generated code and generate reusable XSL stylesheet from it.  
Current version supports only Microsoft Office Open XML .docx format.

Features
----
  - Configurable brackets for tags-placeholders.
  - Basic extension system which helps generating block of content such as Cells or ListItems.
  - Caching XSL template to filesystem.

Requirements
----
Library requires PHP 5.3+ with DOM, XSL, Zip extensions.  
Also depends on ```doctrine2/Lexer``` composer package.

Installation
----
Just install it through composer.
```json
{
    "require": {
       "shadz3rg/phpstamp": "dev-master"
    }
}
```

Usage
----
```php
<?php
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

License
----

LGPL


