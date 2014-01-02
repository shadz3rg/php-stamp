<?php
namespace OfficeML;

use OfficeML\Processor\Lexer;
use OfficeML\Processor\Filters;
use OfficeML\Processor\NodeCollection\DocxNodeCollection;

class Processor
{
    const XSL_NS = 'http://www.w3.org/1999/XSL/Transform';
    const LEFT = 0;
    const RIGHT = 1;

    private $brackets;
    private $lexer;

    /**
     * @param array $brackets
     * @throws Exception\ArgumentsException
     */
    public function __construct(array $brackets = array('[[', ']]'))
    {
        if (count($brackets) !== 2 || array_values($brackets) !== $brackets) {
            throw new Exception\ArgumentsException('Brackets in wrong format.');
        }
        $this->brackets = $brackets;
        $this->lexer = new Lexer($this->brackets);
    }

    /**
     * Wrap document content into xsl template.
     * @param \DOMDocument $document
     * @return \DOMDocument
     */
    public function templateWrapper(\DOMDocument $document)
    {
        $stylesheet = $document->createElementNS(self::XSL_NS, 'xsl:stylesheet');
        $stylesheet->setAttribute('version', '1.0');

        $output = $document->createElementNS(self::XSL_NS, 'xsl:output');
        $output->setAttribute('method', 'xml');
        $output->setAttribute('encoding', 'UTF-8');
        $stylesheet->appendChild($output);

        $template = $document->createElementNS(self::XSL_NS, 'xsl:template');
        $template->setAttribute('match', '//tokens');
        $template->appendChild($document->documentElement);
        $stylesheet->appendChild($template);

        $document->appendChild($stylesheet);

        return $document;
    }

    /**
     * Replace text tokens with xsl elements.
     * @param \DOMDocument $template
     * @return \DOMDocument
     * @throws Exception\TokenException
     */
    public function cache(\DOMDocument $template)
    {
        $xpath = new \DOMXPath($template);

        // TODO Format dependent
        $provider = new DocxNodeCollection($xpath, $this->brackets);

        // Loop trough 'paragraph' nodes (w:p / w:tbl / ...)
        foreach ($provider->getParagraphNodes() as $paragraphNode) {

            $this->lexer->setInput(utf8_decode($paragraphNode->textContent));

            // Length of stripped characters
            $lengthCache = 0;

            while ($token = $this->lexer->next()) {

                $token->setOffset($lengthCache);

                // Left position of 'partial' node inside 'paragraph' node
                $positionOffset = 0;

                // Loop through 'run' nodes (w:r)
                foreach ($provider->getPartialNodes($paragraphNode) as $partNode) {
                    $partNodeLength = mb_strlen($partNode->nodeValue);
                    $partNodePosition = array(
                        self::LEFT => $positionOffset,
                        self::RIGHT => $positionOffset + $partNodeLength
                    );

                    // Check if this token intersects with 'partial' node (left / right bracket)
                    $isLeftInBound = $token->isInclude($partNodePosition[self::LEFT]);
                    $isRightInBound = $token->isInclude($partNodePosition[self::RIGHT]);

                    $textNode = $provider->getTextNode($partNode);

                    // Strip token text part from current node
                    if ($isLeftInBound === true || $isRightInBound === true) {
                        $tokenPosition = $token->getPosition();

                        $start = $tokenPosition[self::RIGHT] - $partNodePosition[self::LEFT];
                        if ($partNodePosition[self::RIGHT] <= $tokenPosition[self::RIGHT]) {
                            $start = 0;
                        }

                        $length = 0;
                        if ($partNodePosition[self::LEFT] <= $tokenPosition[self::LEFT]) {
                            $length = $tokenPosition[self::LEFT] - $positionOffset;
                        } elseif ($partNodePosition[self::RIGHT] >= $tokenPosition[self::RIGHT]) {
                            $length = $partNodePosition[self::RIGHT] - $tokenPosition[self::RIGHT];
                        }

                        $textNode->nodeValue = mb_substr($textNode->nodeValue, $start, $length);

                        // Add xsl logic at left bracket
                        if ($tokenPosition[self::LEFT] >= $partNodePosition[self::LEFT] && $token->isSolved() === false) {
                            $tokenFunc = $token->getFunc();

                            if ($tokenFunc !== null) {
                                if (!isset(Filters::$filters[$tokenFunc['name']])) {
                                    throw new Exception\TokenException('Unknown filter "' . $tokenFunc['name'] . '"');
                                }

                                $func = Filters::$filters[$tokenFunc['name']];
                                $token = call_user_func(
                                    $func,
                                    $token,
                                    $textNode,
                                    $template,
                                    $xpath
                                );
                            } else {
                                $placeholder = $template->createElementNS(self::XSL_NS, 'xsl:value-of');
                                $placeholder->setAttribute('select', '//tokens/' . $token->getValue());
                                $textNode->appendChild($placeholder);
                            }

                            $token->resolve();
                        }
                    }
                    $positionOffset += $partNodeLength;
                }
                $lengthCache += $token->getLength();
            }
        }

        return $template;
    }
}