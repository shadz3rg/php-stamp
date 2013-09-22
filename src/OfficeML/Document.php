<?php
namespace OfficeML;

class Document {

    const DOC_CONTENT = 'word/document.xml';
    const COMPILED_DIR = '/compiled';

    private $filePath;
    private $compiledFilePath;

    function __construct($filePath) {
        if (!is_file($filePath)) {
            throw new OpenXMLException('File not found');
        }

        $this->filePath = $filePath;

        $file = pathinfo($this->filePath);
        $this->compiledFilePath = $file['dirname'] . self::COMPILED_DIR . '/' . $file['filename'] . '.' . $file['extension'];
    }

    public function isCompiled() {
        return (file_exists($this->compiledFilePath) && (filemtime($this->compiledFilePath) > (time() - 60 * 5 )));
    }

    public function getContent() {
        // Здесь обращаемя к файлу в архиве
        //return file_get_contents($this->filePath);
        $doc = new \DOMDocument();
        $doc->preserveWhiteSpace = false;
        $doc->load($this->filePath);
        return $doc;
    }

    public function getTemplate() {
        $template = new \DOMDocument();
        $template->load($this->compiledFilePath);
        return $template;
    }

    public function getCompiledFilePath() {
        return $this->compiledFilePath;
    }
}