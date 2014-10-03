<?php

namespace OfficeML;

use OfficeML\Document\Document;
use OfficeML\Document\DocumentInterface;
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
     * @param DocumentInterface $document
     * @param array $values
     * @return Result
     */
    public function render(DocumentInterface $document, array $values)
    {
        $contentFile = $document->extract($this->cachePath, $this->debug);

        $template = new \DOMDocument('1.0', 'UTF-8');
        $template->load($contentFile);

        // process xml document into xsl template
        if ($template->documentElement->nodeName !== 'xsl:stylesheet') {

            // prepare xml document
            Processor::escapeXsl($template);

            $document->cleanup($template);

            // process prepared xml document
            Processor::wrapIntoTemplate($template);

            // find node list with text and handle tags TODO query contains bracket
            $nodeList = XMLHelper::queryTemplate($template, $document->getNodePath());
            $this->handleTags($nodeList, $document);

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

        Processor::undoEscapeXsl($output);

        return new Result($output, $document);
    }

    private function handleTags(\DOMNodeList $nodeList, DocumentInterface $document)
    {
        $lexer = new Lexer($this->brackets);
        $mapper = new TagMapper;

        /** @var $node \DOMElement */
        foreach ($nodeList as $node) {
            $decodedValue = utf8_decode($node->nodeValue);
            $lexer->setInput($decodedValue);

            while ($tag = $mapper->parse($lexer)) {

                foreach ($tag->getFunctions() as $function) {
                    $expression = $document->getExpression($function['function']);
                    $expression->insertTemplateLogic($function['arguments'], $node, $node->ownerDocument);
                }

                Processor::insertTemplateLogic($tag, $node, $node->ownerDocument);
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