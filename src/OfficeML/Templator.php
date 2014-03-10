<?php

namespace OfficeML;

use OfficeML\Document\Document;

class Templator
{
    public $debug = false;

    private $cachePath;
    private $processor;
    private $brackets;

    public function __construct($cachePath, $brackets = array('[[', ']]'))
    {
        if (!is_dir($cachePath)) {
            throw new Exception\ArgumentsException('Cache path "' . $cachePath . '" unreachable.');
        }
        if (!is_writable($cachePath)) {
            throw new Exception\ArgumentsException('Cache path "' . $cachePath . '" not writable.');
        }
        if (count($brackets) !== 2 || array_values($brackets) !== $brackets) {
            throw new Exception\ArgumentsException('Brackets in wrong format.');
        }

        $this->cachePath = $cachePath;
        $this->brackets = $brackets;

        $this->processor = new Processor($this->brackets);
    }

    public function render(Document $document, $values)
    {
        $contentFile = $document->extract($this->cachePath, $this->debug);

        $template = new \DOMDocument('1.0', 'UTF-8');
        $template->load($contentFile);

        // Process document into template
        if ($template->documentElement->nodeName !== 'xsl:stylesheet') {

            $this->processor->wrapIntoTemplate($template);

            $this->processor->insertTemplateLogic($template, $document->getTokenCollection($template));

            $template->save($contentFile);

            // FIXME Workaround for disappeared xml: attributes, reload as temporary fix
            $template->load($contentFile);
        }

        // Collide w/ values
        $xslt = new \XSLTProcessor();
        $xslt->importStylesheet($template);

        $output = $xslt->transformToDoc(
            $this->assign($values)
        );

        if ($this->debug === true) {
            $output->preserveWhiteSpace = true;
            $output->formatOutput = true;
        }

        return new Result($output, $document);
    }

    private function assign(array $tokens)
    {
        $document = new \DOMDocument('1.0', 'UTF-8');

        $tokensNode = $document->createElement('tokens');
        $document->appendChild($tokensNode);

        Helper::xmlEncode($tokens, $tokensNode, $document);

        return $document;
    }
}