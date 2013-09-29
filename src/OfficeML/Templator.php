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

    private function cache($templateFile)
    {
        $template = new \DOMDocument('1.0', 'UTF-8');

        if ($this->debug === true) {
            $template->preserveWhiteSpace = true;
            $template->formatOutput = true;
        }

        $template->load($templateFile);
        $this->processor->cache($template);
        $template->save($templateFile);

        // FIXME Workaround for disappeared xml: attributes
        $template->load($templateFile);

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
        $tempArchive = tempnam(sys_get_temp_dir(), 'doc');

        if (copy($this->document->documentPath, $tempArchive)) {
            $zip = new \ZipArchive();
            $zip->open($tempArchive);
            $zip->addFromString(self::DOC_CONTENT, $document->saveXML());
            $zip->close();

            header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
            header('Content-Disposition: attachment;filename="' . $this->document->documentName . '.docx"');

            // Экспереминтально доказано - необходимы ob_clean() и exit;
            ob_clean();
            readfile($tempArchive);
            unlink($tempArchive);
            exit;
        }
    }
}