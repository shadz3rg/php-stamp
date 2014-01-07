<?php

namespace OfficeML;

class Templator
{
    public $debug = false;

    private $document;
    private $processor;
    private $cachePath;
    private $values;

    /**
     * Constructor.
     *
     * @param Document $document
     * @param Processor $processor
     * @param string $cachePath
     * @throws Exception\ArgumentsException
     */
    public function __construct(Document $document, Processor $processor, $cachePath)
    {
        $this->values = new \DOMDocument('1.0', 'UTF-8');

        $this->document = $document;
        $this->document->extract($cachePath, $this->debug);

        $this->processor = $processor;

        if (!is_dir($cachePath)) {
            throw new Exception\ArgumentsException('Cache path unreachable.');
        }
        $this->cachePath = $cachePath;
    }

    /**
     * Self factory init.
     *
     * @param $documentPath
     * @param string $cachePath
     * @param array $brackets
     * @return Templator
     */
    public static function create($documentPath, $cachePath, $brackets = array('[[', ']]'))
    {
        return new self(
            new Document($documentPath),
            new Processor($brackets),
            $cachePath
        );
    }

    /**
     * Assign values with multidimensional associative array.
     *
     * @param array $tokens
     * @return void
     */
    public function assign(array $tokens)
    {
        $tokensNode = $this->values->createElement('tokens');
        $this->values->appendChild($tokensNode);

        Helper::xmlEncode($tokens, $tokensNode, $this->values);
    }

    /**
     * Convert document into template and assign given values.
     *
     * @return \DOMDocument
     */
    public function output()
    {
        // Loading
        $template = $this->document->content;
        $templateFile = $this->document->contentPath;

        // Process document into template
        if ($template->documentElement->nodeName !== 'xsl:stylesheet') {
            $this->processor->cache(
                $this->processor->templateWrapper($template)
            );
            $template->save($templateFile);

            // FIXME Workaround for disappeared xml: attributes, reload as temporary fix
            $template->load($templateFile);
        }

        // Collide w/ values
        $xslt = new \XSLTProcessor();
        $xslt->importStylesheet($template);
        $output = $xslt->transformToDoc($this->values);

        if ($this->debug === true) {
            $output->preserveWhiteSpace = true;
            $output->formatOutput = true;
        }

        return $output;
    }

    /**
     * Prepare document for downloading.
     *
     * @return void
     */
    public function download()
    {
        $document = $this->output();
        $tempArchive = tempnam(sys_get_temp_dir(), 'doc');

        if (copy($this->document->documentPath, $tempArchive) === true) {
            $zip = new \ZipArchive();
            $zip->open($tempArchive);
            $zip->addFromString(self::DOC_CONTENT, $document->saveXML());
            $zip->close();

            header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
            header('Content-Disposition: attachment;filename="' . $this->document->documentName . '.docx"');

            // Send file - required ob_clean() & exit;
            ob_clean();
            readfile($tempArchive);
            unlink($tempArchive);
            exit;
        }
    }
}