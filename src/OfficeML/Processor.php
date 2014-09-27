<?php
namespace OfficeML;

use OfficeML\Processor\Lexer;
use OfficeML\Processor\Filters;
use OfficeML\NodeCollection\WordNodeCollection;
use OfficeML\Processor\Tag;
use OfficeML\Processor\TokenCollection;
use OfficeML\Processor\Token;

class Processor
{
    const XSL_NS = 'http://www.w3.org/1999/XSL/Transform';
    const LEFT = 0;
    const RIGHT = 1;

    private $brackets;
    private $lexer;

    /**
     * @param array $brackets
     * @throws Exception\InvalidArgumentException
     */
    public function __construct(array $brackets)
    {
        $this->brackets = $brackets;

        $this->lexer = new Lexer($this->brackets);
    }

    /**
     * Wrap document content into xsl template.
     * @param \DOMDocument $document
     * @return \DOMDocument
     */
    public function wrapIntoTemplate(\DOMDocument $document)
    {
        $stylesheet = $document->createElementNS(self::XSL_NS, 'xsl:stylesheet');
        $stylesheet->setAttribute('version', '1.0');

        $output = $document->createElementNS(self::XSL_NS, 'xsl:output');
        $output->setAttribute('method', 'xml');
        $output->setAttribute('encoding', 'UTF-8'); // TODO variable encoding?
        $stylesheet->appendChild($output);

        $template = $document->createElementNS(self::XSL_NS, 'xsl:template');
        $template->setAttribute('match', '/tokens');
        $template->appendChild($document->documentElement);
        $stylesheet->appendChild($template);

        $document->appendChild($stylesheet);

        return $document;
    }

    public static function insertTemplateLogic(Tag $tag, \DOMNode $node)
    {
        $template = $node->ownerDocument;

        $nodeValue = utf8_decode($node->nodeValue);
        $tagFragment = mb_substr($nodeValue, $tag->getPosition(), $tag->getLength());

        $node->nodeValue = ''; // reset

        // before [[tag]] after
        $nodeValueParts = explode($tagFragment, $nodeValue, 2); // multiple token in one node

        // text before
        $before = $template->createTextNode($nodeValueParts[0]);
        $node->appendChild($before);

        // add xsl logic TODO Functions
        $placeholder = $template->createElementNS(self::XSL_NS, 'xsl:value-of');
        $placeholder->setAttribute('select', '/tokens/' . $tag->getXmlPath());
        $node->appendChild($placeholder);

        // text after
        $after = $template->createTextNode($nodeValueParts[1]);
        $node->appendChild($after);

        return $tagFragment;
    }
}