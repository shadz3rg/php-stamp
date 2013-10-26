<?php
namespace OfficeML;

class Processor
{
    const XSL_NS = 'http://www.w3.org/1999/XSL/Transform';
    const LEFT_BRACKET = 0;
    const RIGHT_BRACKET = 1;

    /**
     * @var array
     */
    private $brackets;

    /**
     * @param array $brackets
     * @throws Exception\ArgumentsException
     */
    public function __construct(array $brackets = array('[[', ']]')) {
        if (count($brackets) !== 2 || array_values($brackets) !== $brackets) {
            throw new Exception\ArgumentsException('Brackets in wrong format.');
        }
        $this->brackets = $brackets;
    }

    /**
     * Wrap document content into xsl template/
     * @param \DOMDocument $document
     * @return \DOMDocument
     */
    private function templateWrapper(\DOMDocument $document)
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
     * @param \DOMDocument $document
     * @return \DOMDocument
     * @throws Exception\TokenException
     */
    public function cache(\DOMDocument $document)
    {
        $template = $this->templateWrapper($document);

        // Search for tokens
        $xpath = new \DOMXPath($template);
        $query = sprintf('//w:body/*[contains(., "%s")][contains(., "%s")]',
            $this->brackets[self::LEFT_BRACKET],
            $this->brackets[self::RIGHT_BRACKET]
        );

        $nodes = $xpath->query($query);
        if ($nodes->length === 0) {
            throw new Exception\TokenException('Tokens not found.');
        }

        $lexer = new Lexer($this->brackets);

        // Loop trough 'paragraph' nodes
        for ($i = 0; $i < $nodes->length; $i++) {

            $paragraphNode = $nodes->item($i); // w:p
            $lexer->setInput(utf8_decode($paragraphNode->textContent));

            // Length of stripped tags
            $lengthCache = 0;

            // Loop through found tokens
            while ($token = $lexer->next()) {

                // TODO Сделать покрасивше
                $token['position'][self::LEFT_BRACKET] -= $lengthCache;
                $token['position'][self::RIGHT_BRACKET] -= $lengthCache;

                $partNodes = $xpath->query('.//w:r', $paragraphNode);

                // Left position of 'partial' node inside 'paragraph' node
                $positionOffset = 0;

                // Loop through 'run' nodes
                for ($c = 0; $c < $partNodes->length; $c++) {
                    $partNode = $partNodes->item($c); //w:r
                    $partLength = mb_strlen($partNode->nodeValue);

                    $position = array(
                        self::LEFT_BRACKET => $positionOffset,
                        self::RIGHT_BRACKET => $positionOffset + $partLength
                    );

                    // Check if this 'partial' node contents left / right bracket
                    $isLeftInBound = (
                        $token['position'][self::LEFT_BRACKET] <= $position[self::LEFT_BRACKET] &&
                        $position[self::LEFT_BRACKET] <= $token['position'][self::RIGHT_BRACKET]
                    );
                    $isRightInBound = (
                        $token['position'][self::LEFT_BRACKET] <= $position[self::RIGHT_BRACKET] &&
                        $position[self::RIGHT_BRACKET] <= $token['position'][self::RIGHT_BRACKET]
                    );

                    if ($isLeftInBound === true || $isRightInBound === true) {
                        $textNodes = $xpath->query('w:t', $partNode);

                        // For testing purpose
                        if ($textNodes->length !== 1) {
                            throw new Exception\TokenException('Multiple w:t');
                        }
                        $textNode = $textNodes->item(0);

                        // Strip token text
                        $start = $token['position'][self::RIGHT_BRACKET] - $position[self::LEFT_BRACKET];
                        if ($position[self::RIGHT_BRACKET] <= $token['position'][self::RIGHT_BRACKET]) {
                            $start = 0;
                        }

                        $length = 0;
                        if ($position[self::LEFT_BRACKET] <= $token['position'][self::LEFT_BRACKET]) {
                            $length = $token['position'][self::LEFT_BRACKET] - $positionOffset;
                        } elseif ($position[self::RIGHT_BRACKET] >= $token['position'][self::RIGHT_BRACKET]) {
                            $length = $position[self::RIGHT_BRACKET] - $token['position'][self::RIGHT_BRACKET];
                        }

                        $textNode->nodeValue = mb_substr($textNode->nodeValue, $start, $length);

                        // Insert 'value-of' in beginning of token
                        echo $token['value'] . '-' . $position[self::LEFT_BRACKET] . '-' . $token['position'][self::LEFT_BRACKET] . PHP_EOL;
                        if ($position[self::LEFT_BRACKET] < $token['position'][self::LEFT_BRACKET]) {
                            $placeholder = $template->createElementNS(self::XSL_NS, 'xsl:value-of');
                            $placeholder->setAttribute('select', '//tokens/' . $token['value']);
                            $textNode->appendChild($placeholder);
                        }
                    }
                    $positionOffset += $partLength;
                }
                $lengthCache += mb_strlen($token['token']);
            }
        }
        return $template;
    }
}