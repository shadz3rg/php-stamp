<?php

namespace PHPStamp;

use PHPStamp\Document\DocumentInterface;
use PHPStamp\Processor\Lexer;
use PHPStamp\Processor\TagMapper;

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
        // fill with values
        $xslt = new \XSLTProcessor();

        $template = $this->getTemplate($document);
        $xslt->importStylesheet($template);

        $content = $xslt->transformToDoc(
            $this->createValuesDocument($values)
        );

        Processor::undoEscapeXsl($content);

        return new Result($content, $document);
    }

    private function getTemplate(DocumentInterface $document)
    {
        $contentFile = $document->extract($this->cachePath, $this->debug);

        $template = new \DOMDocument('1.0', 'UTF-8');
        $template->load($contentFile);

        // process xml document into xsl template
        if ($template->documentElement->nodeName !== 'xsl:stylesheet') {
            $this->createTemplate($template, $document);

            // cache template FIXME workaround for disappeared xml: attributes, reload as temporary fix
            $template->save($contentFile);
            $template->load($contentFile);
        }

        return $template;
    }

    private function createTemplate(\DOMDocument $template, DocumentInterface $document)
    {
        // prepare xml document
        Processor::escapeXsl($template);

        $document->cleanup($template);

        // process prepared xml document
        Processor::wrapIntoTemplate($template);

        // find node list with text and handle tags
        $query = $document->getNodePath();
        $query .= sprintf(
            '[contains(text(), "%s") and contains(text(), "%s")]',
            $this->brackets[0],
            $this->brackets[1]
        );
        $nodeList = XMLHelper::queryTemplate($template, $query);
        $this->searchAndReplace($nodeList, $document);
    }

    private function searchAndReplace(\DOMNodeList $nodeList, DocumentInterface $document)
    {
        $lexer = new Lexer($this->brackets);
        $mapper = new TagMapper;

        /** @var $node \DOMElement */
        foreach ($nodeList as $node) {
            $lexer->setInput($node->nodeValue);

            while ($tag = $mapper->parse($lexer)) {

                foreach ($tag->getFunctions() as $function) {
                    $expression = $document->getExpression($function['function'], $tag);
                    $expression->execute($function['arguments'], $node);
                }

                // insert simple value-of
                if ($tag->hasFunctions() === false) {
                    $absolutePath = '/' . Processor::VALUE_NODE . '/' . $tag->getXmlPath();
                    Processor::insertTemplateLogic($tag->getTextContent(), $absolutePath, $node);
                }
            }
        }
    }

    /**
     * Create DOMDocument and encode array into XML recursively
     *
     * @param array $values
     * @return \DOMDocument
     */
    private function createValuesDocument(array $values)
    {
        $document = new \DOMDocument('1.0', 'UTF-8');

        $tokensNode = $document->createElement(Processor::VALUE_NODE);
        $document->appendChild($tokensNode);

        XMLHelper::xmlEncode($values, $tokensNode, $document);

        return $document;
    }
}