<?php

namespace OfficeML;

use OfficeML\Document\Document;
use OfficeML\Document\DocumentInterface;
use OfficeML\Processor\TagMapper;

class Templator
{
    public $debug = false;

    private $cachePath;
    private $brackets;

    public function __construct($cachePath, $brackets = array('[[', ']]'))
    {
        if (!is_dir($cachePath)) {
            throw new Exception\InvalidArgumentException('Cache path "' . $cachePath . '" unreachable.');
        }
        if (!is_writable($cachePath)) {
            throw new Exception\InvalidArgumentException('Cache path "' . $cachePath . '" not writable.');
        }
        if (count($brackets) !== 2 || array_values($brackets) !== $brackets) {
            throw new Exception\InvalidArgumentException('Brackets are in wrong format.');
        }

        $this->cachePath = $cachePath;
        $this->brackets = $brackets;
    }

    public function render(Document $document, array $values)
    {
        $contentFile = $document->extract($this->cachePath, $this->debug);

        $template = new \DOMDocument('1.0', 'UTF-8');
        $template->load($contentFile);

        // process document into template
        if ($template->documentElement->nodeName !== 'xsl:stylesheet') {

            $nodeStructure = $document->getNodeStructure();

            // fix node breaks
            $cleaner = new Cleanup(
                $template,
                $nodeStructure[Document::XPATH_PARAGRAPH],
                $nodeStructure[Document::XPATH_RUN],
                $nodeStructure[Document::XPATH_PROPERTY],
                $nodeStructure[Document::XPATH_TEXT]
            );
            $template = $cleaner->cleanup();

            // process fixed document
            $processor = new Processor($this->brackets);
            $template = $processor->wrapIntoTemplate($template);

            // find tokens
            $mapper = new TagMapper($template, $this->brackets);
            //$nodeCollection = $mapper->parseForTokens($document->getTextQuery());

            // insert xsl logic
            //$template = $processor->insertTemplateLogic($template, $nodeCollection);

            $template->save($contentFile);

            // FIXME Workaround for disappeared xml: attributes, reload as temporary fix
            $template->load($contentFile);
        }

        // Collide w/ values
        //$xslt = new \XSLTProcessor();
        //$xslt->importStylesheet($template);

        //$output = $xslt->transformToDoc(
            //$this->assign($values)
        //);
        $output = $template;

        if ($this->debug === true) {
            $output->preserveWhiteSpace = true;
            $output->formatOutput = true;
        }

        return new Result($output, $document);
    }

    /**
     * Create DOMDocument and encode array
     * @param array $tokens
     * @return \DOMDocument
     */
    private function assign(array $tokens)
    {
        $document = new \DOMDocument('1.0', 'UTF-8');

        $tokensNode = $document->createElement('tokens');
        $document->appendChild($tokensNode);

        Helper::xmlEncode($tokens, $tokensNode, $document);

        return $document;
    }
}