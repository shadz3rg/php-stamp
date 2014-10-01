<?php

namespace OfficeML;

use OfficeML\Document\Document;
use OfficeML\Processor\Lexer;
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

    /**
     * Process given document into template and render it with given values.
     *
     * @param Document $document
     * @param array $values
     * @return Result
     */
    public function render(Document $document, array $values)
    {
        $contentFile = $document->extract($this->cachePath, $this->debug);

        $template = new \DOMDocument('1.0', 'UTF-8');
        $template->load($contentFile);

        // process xml document into xsl template
        if ($template->documentElement->nodeName !== 'xsl:stylesheet') {

            // fix node breaks
            $cleaner = new Cleanup(
                $template,
                $document->getNodeQuery(Document::XPATH_PARAGRAPH, true),
                $document->getNodeQuery(Document::XPATH_RUN),
                $document->getNodeQuery(Document::XPATH_RUN_PROPERTY),
                $document->getNodeQuery(Document::XPATH_TEXT)
            );

            // prepare xml document
            Processor::escapeXsl($template);
            $cleaner->hardcoreCleanup();
            $cleaner->cleanup();

            // process prepared xml document
            Processor::wrapIntoTemplate($template);

            // find node list with text and handle tags TODO query contains bracket
            $nodeList = XMLHelper::queryTemplate($template, $document->getNodePath());
            $this->handleTags($nodeList);

            // cache template
            $template->save($contentFile);

            // FIXME workaround for disappeared xml: attributes, reload as temporary fix
            $template->load($contentFile);
        }

        // fill with values
        $xslt = new \XSLTProcessor();
        $xslt->importStylesheet($template);

        $output = $xslt->transformToDoc(
            $this->assign($values)
        );

        Processor::undoEscapeXsl($template);

        return new Result($output, $document);
    }

    private function handleTags(\DOMNodeList $nodeList)
    {
        $lexer = new Lexer($this->brackets);
        $mapper = new TagMapper;

        foreach ($nodeList as $node) {
            $decodedValue = utf8_decode($node->nodeValue);
            $lexer->setInput($decodedValue);

            while ($tag = $mapper->parse($lexer)) {
                Processor::insertTemplateLogic($tag, $node);
            }
        }
    }

    /**
     * Create DOMDocument and encode array into XML recursively
     *
     * @param array $tokens
     * @return \DOMDocument
     */
    private function assign(array $tokens)
    {
        $document = new \DOMDocument('1.0', 'UTF-8');

        $tokensNode = $document->createElement(Processor::VALUES_PATH);
        $document->appendChild($tokensNode);

        XMLHelper::xmlEncode($tokens, $tokensNode, $document);

        return $document;
    }
}