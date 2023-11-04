<?php

namespace PHPStamp;

use PHPStamp\Core\CommentTransformer;
use PHPStamp\Document\DocumentInterface;
use PHPStamp\Processor\Lexer;
use PHPStamp\Processor\TagMapper;

class Templator
{
    /**
     * Enable debug mode to generate template with every render call.
     *
     * @var bool
     */
    public $debug = false;

    /**
     * Enable track mode to generate template with every original document change.
     *
     * @var bool
     */
    public $trackDocument = false;

    /**
     * Writable path to store compiled template.
     *
     * @var string
     */
    private $cachePath;

    /**
     * Customizable placeholder brackets.
     *
     * @var array
     */
    private $brackets;

    /**
     * Create a new Templator.
     *
     * @param string $cachePath writable path to store compiled template
     * @param array  $brackets  customizable placeholder brackets
     *
     * @throws Exception\InvalidArgumentException
     */
    public function __construct($cachePath, $brackets = ['[[', ']]'])
    {
        if (!is_dir($cachePath)) {
            throw new Exception\InvalidArgumentException('Cache path "'.$cachePath.'" unreachable.');
        }
        if (!is_writable($cachePath)) {
            throw new Exception\InvalidArgumentException('Cache path "'.$cachePath.'" not writable.');
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
     * @param DocumentInterface $document document to render
     * @param array             $values   multidimensional array with values to replace placeholders
     *
     * @return Result
     *
     * @throws Exception\InvalidArgumentException
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

    /**
     * Cache control for document template.
     *
     * @param DocumentInterface $document document to render
     *
     * @return \DOMDocument XSL stylesheet
     *
     * @throws Exception\InvalidArgumentException
     */
    private function getTemplate(DocumentInterface $document)
    {
        $overwrite = false;
        if ($this->trackDocument === true) {
            $overwrite = $this->compareHash($document);
        }

        $contentFile = $document->extract($this->cachePath, $this->debug || $overwrite);

        $template = new \DOMDocument('1.0', 'UTF-8');
        $template->load($contentFile);

        // process xml document into xsl template
        if ($template->documentElement->nodeName !== 'xsl:stylesheet') {
            $this->createTemplate($template, $document);
            $this->storeComment($template, $document);

            // cache template FIXME workaround for disappeared xml: attributes, reload as temporary fix
            $template->save($contentFile);
            $template->load($contentFile);
        }

        return $template;
    }

    /**
     * Create reusable template from XML content file.
     *
     * @param \DOMDocument      $template main content file
     * @param DocumentInterface $document document to render
     */
    public function createTemplate(\DOMDocument $template, DocumentInterface $document)
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

    /**
     * Search and replace placeholders with XSL logic.
     *
     * @param \DOMNodeList      $nodeList list of nodes having at least one placeholder
     * @param DocumentInterface $document document to render
     */
    private function searchAndReplace(\DOMNodeList $nodeList, DocumentInterface $document)
    {
        $lexer = new Lexer($this->brackets);
        $mapper = new TagMapper();

        /** @var $node \DOMElement */
        foreach ($nodeList as $node) {
            $decodedValue = utf8_decode($node->nodeValue);

            $lexer->setInput($decodedValue);

            while ($tag = $mapper->parse($lexer)) {
                foreach ($tag->getFunctions() as $function) {
                    $expression = $document->getExpression($function['function'], $tag);
                    $expression->execute($function['arguments'], $node);
                }

                // insert simple value-of
                if ($tag->hasFunctions() === false) {
                    $absolutePath = '/'.Processor::VALUE_NODE.'/'.$tag->getXmlPath();
                    Processor::insertTemplateLogic($tag->getTextContent(), $absolutePath, $node);
                }
            }
        }
    }

    /**
     * Create DOMDocument and encode multidimensional array into XML recursively.
     *
     * @param array $values multidimensional array
     *
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

    /**
     * Fetch original file hash stored in template comment and compare it with actual file hash.
     *
     * @param DocumentInterface $document document to render
     *
     * @return bool Document was updated?
     */
    private function compareHash(DocumentInterface $document)
    {
        $overwrite = false;

        $contentPath = $this->cachePath.$document->getDocumentName().'/'.$document->getContentPath();
        if (file_exists($contentPath) === true) {
            $template = new \DOMDocument('1.0', 'UTF-8');
            $template->load($contentPath);

            $query = new \DOMXPath($template);
            $commentList = $query->query('/xsl:stylesheet/comment()');

            if ($commentList->length === 1) {
                $commentNode = $commentList->item(0);

                $commentContent = $commentNode->nodeValue;
                $commentContent = trim($commentContent);

                $transformer = new CommentTransformer();
                $contentMeta = $transformer->reverseTransformer($commentContent);

                if ($document->getDocumentHash() !== $contentMeta['document_hash']) {
                    $overwrite = true;
                }
            }
        }

        return $overwrite;
    }

    /**
     * Represent META data as string and store in template.
     *
     * @param \DOMDocument      $template XSL stylesheet
     * @param DocumentInterface $document document to render
     */
    private function storeComment(\DOMDocument $template, DocumentInterface $document)
    {
        $meta = [
            'generation_date' => date('Y-m-d H:i:s'),
            'document_hash' => $document->getDocumentHash(),
        ];

        $transformer = new CommentTransformer();
        $commentContent = $transformer->transform($meta);

        $commentNode = $template->createComment($commentContent);
        $template->documentElement->appendChild($commentNode);
    }
}
