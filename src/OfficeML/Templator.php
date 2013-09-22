<?php
namespace OfficeML;

class Templator {
    const DOC_CONTENT = 'word/document.xml';

    public $debug = false;
    private $cachePath;

    private $document;
    private $processor;
    private $values;

    public function __construct(Document $document, Processor $processor, $cachePath){
        $this->document = $document;
        $this->processor = $processor;
        $this->cachePath = $cachePath;

        $this->values = new \DOMDocument();
    }

    public static function create($documentPath, $cachePath, $brackets = array('[[', ']]'))
    {
        return new self(
            new Document($documentPath),
            new Processor($brackets),
            $cachePath
        );
    }

    public function cache()
    {
        $templateFile = $this->document->extract($this->cachePath, self::DOC_CONTENT);

        $template = new \DOMDocument('1.0', 'UTF-8');

        if ($this->debug === true) {
            $template->preserveWhiteSpace = true;
            $template->formatOutput = true;
        }

        $template->load($templateFile);

        $template = $this->processor->cache($template);
        return $template->saveXML();
    }


    public function assign(array $tokens) {
       /*
        if ($this->doc->isCompiled() === false || $this->debug === true) {
            $this->processor->compile(
                $this->doc->getCompiledFilePath(),
                $this->doc->getContent()
            );
        }

        Helper::xmlEncode(array('tokens' => $tokens), $this->values, $this->values);

        // Processing values
        $xslt = new \XsltProcessor();
        $xslt->importStylesheet($this->doc->getTemplate());

        $result = $xslt->transformToDoc($this->values);
        $result->formatOutput = true;
        return $result->saveXML();*/
    }
}