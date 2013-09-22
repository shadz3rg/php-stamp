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

    public function cache($templateFile)
    {
        $template = new \DOMDocument('1.0', 'UTF-8');

        if ($this->debug === true) {
            $template->preserveWhiteSpace = true;
            $template->formatOutput = true;
        }

        $template->load($templateFile);

        $template = $this->processor->cache($template);
        //$template->save($templateFile);
        return $template;
    }

    public function assign(array $tokens) {
        $tokensNode = $this->values->createElement('tokens');
        $this->values->appendChild($tokensNode);

        Helper::xmlEncode($tokens, $tokensNode, $this->values);
    }

    public function output()
    {
        // TODO Cache time!
        $templateFile = $this->document->extract($this->cachePath, self::DOC_CONTENT);
        $template = $this->cache($templateFile);

        $xslt = new \XSLTProcessor();
        $xslt->importStylesheet($template);

        return $xslt->transformToDoc($this->values);
    }

    public function download()
    {
        $document = $this->output();

        //$tempArchive = tempnam(sys_get_temp_dir(), 'doc');
$result = $this->cachePath . 'z.zip';

        if (copy($this->document->documentPath, $result)) {

            $zip = new \ZipArchive();
            $zip->open($result);
            $zip->addFromString(self::DOC_CONTENT, $document->saveXML());
            $zip->close();

            header("Content-Type: application/zip");
            header("Content-Transfer-Encoding: Binary");
            header("Content-Length: " . filesize($result));
            header("Content-Disposition: attachment; filename=" . $this->document->documentName);

            ob_clean();
            readfile($result);
        }
    }
}