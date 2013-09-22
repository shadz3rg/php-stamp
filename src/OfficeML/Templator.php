<?php
namespace OfficeML;

class Templator {
    public $debug = false;

    private $doc;
    private $processor;

    function __construct(Document $doc, Processor $processor){
        $this->doc = $doc;
        $this->processor = $processor;
    }

    public function getTokens() {
        return $this->processor->getTokens( $this->doc->getContent() );
    }

    public function assign(array $tokens) {
        if ($this->doc->isCompiled() === false || $this->debug === true) {
            $this->processor->compile(
                $this->doc->getCompiledFilePath(),
                $this->doc->getContent()
            );
        }

        $xml = new \DOMDocument();
        Helper::xmlEncode(array('tokens' => $tokens), $xml, $xml);

        // Processing values
        $xslt = new \XsltProcessor();
        $xslt->importStylesheet($this->doc->getTemplate());

        $result = $xslt->transformToDoc($xml);
        $result->formatOutput = true;
        return $result->saveXML();
    }

    public function output() {

    }
}