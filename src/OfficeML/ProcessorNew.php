<?php
namespace OfficeML;

use OfficeML\Processor\Lexer;
use OfficeML\Processor\Filters;
use OfficeML\NodeCollection\WordNodeCollection;
use OfficeML\Processor\TokenCollection;
use OfficeML\Processor\Token;

class ProcessorNew
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

    public function insertTemplateLogic(\DOMDocument $template, TokenCollection $tokenCollection, $stripOnly = false)
    {
        $xpath = new \DOMXPath($template);

        /** @var $token Processor\Token */
        foreach ($tokenCollection as $token) {
            $containerNode = $token->getContainerNode();
            $valueParts = explode($token->getToken(), $containerNode->nodeValue, 2); // multiple token in one node

            $containerNode->nodeValue = '';

            $before = $template->createTextNode($valueParts[0]);
            $containerNode->appendChild($before);

            // create node here
            if ($token->hasFunc() === true) {
                if (!isset(Filters::$filters[$token->getFuncName()])) {
                    throw new Exception\TokenException('Unknown filter "' . $token->getFuncName() . '"');
                }

                /*$func = Filters::$filters[$token->getFuncName()];
                $token = call_user_func(
                    $func,
                    $token,
                    $textNode,
                    $template,
                    $xpath
                );*/

            } else {
                $placeholder = $template->createElementNS(self::XSL_NS, 'xsl:value-of');
                $placeholder->setAttribute('select', '/tokens/' . $token->getValue());
                $containerNode->appendChild($placeholder);
            }

            $after = $template->createTextNode($valueParts[1]);
            $containerNode->appendChild($after);
        }

        return $template;
    }














    private function findTokenPadding($tokenLeft, $tokenRight, $nodeLeft, $nodeRight)
    {
        //      | n o d e | n o d e | n o d e |
        //      | s | t o k e n | e |

        // Если между началом ноды и началом токена нет положительного промежутка, то обрезаем с начала ноды
        $s      = $tokenLeft - $nodeLeft;
        $start  = max($s, 0);

        // Если между концом ноды ноды и концом токена положительного промежутка, то округляем до нуля
        $e      = $nodeRight - $tokenRight;
        $end    = min( max($e, 0), ($nodeRight - $nodeLeft));

        return array(
            self::LEFT => $start,
            self::RIGHT => $end
        );
    }

    public function throughTokens(\DOMDocument $template, TokenCollection $tokenCollection)
    {
        $runQuery = './/run';
        $textQuery = 'text';

        $xpath = new \DOMXPath($template);

        /** @var $token Processor\Token */
        foreach ($tokenCollection as $token) {
            $targetNode = $this->strip($token, $template, $xpath);

        }
    }

    public function strip(Token $token, \DOMDocument $template, \DOMXPath $xpath)
    {
        // Позиция текущей ноды относительно контейнера
        $positionOffset = 0;

        $partialNodes = $xpath->query('.//run', $token->getContainerNode());

        foreach ($partialNodes as $node) {

            $nodeLength = mb_strlen($node->nodeValue);
            $nodePosition = array(
                self::LEFT => $positionOffset,
                self::RIGHT => $positionOffset + $nodeLength
            );

            // Если отрезок токена имеет общие точки с нодой то вырезаем присутствующую часть токена
            if ($token->intersection($nodePosition[self::LEFT], $nodePosition[self::RIGHT]) === true) {

                $tokenPosition = $token->getPosition();

                // Нода
                $textNode = $xpath->query('text', $node)->item(0);
                $nodeValue = $textNode->nodeValue;
                $textNode->nodeValue = '';

                // Отступы от фрагмента токена, между ними вырезаем
                $tokenPadding = $this->findTokenPadding(
                    $tokenPosition[self::LEFT],
                    $tokenPosition[self::RIGHT],
                    $nodePosition[self::LEFT],
                    $nodePosition[self::RIGHT]
                );

                // Текст до фрагмента токена
                if ($tokenPadding[self::LEFT] > 0) {
                    $before = $template->createTextNode(mb_substr($nodeValue, 0, $tokenPadding[self::LEFT]));
                    $textNode->appendChild($before);
                }

                // Добавляем логику если мы в нужной ноде с левым краем токена
                $between = $nodePosition[self::LEFT] <= $tokenPosition[self::LEFT] && $tokenPosition[self::LEFT] <= $nodePosition[self::RIGHT];
                if ($token->isSolved() === false && $between === true) {
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

                // Текст после фрагмента токена
                if ($tokenPadding[self::RIGHT] > 0) {
                    $after = $template->createTextNode(mb_substr($nodeValue, -$tokenPadding[self::RIGHT]));
                    $textNode->appendChild($after);
                }
            }

            // Сдвигаем оффсет на изначальную длину ноды
            $positionOffset += $nodeLength;
        }
    }

    public function logic(Token $token)
    {

    }

    public function insertTemplateLogic_(\DOMDocument $template, TokenCollection $tokenCollection, $stripOnly = false)
    {
        $xpath = new \DOMXPath($template);

        /** @var $token Processor\Token */
        foreach ($tokenCollection as $token) {

            // Позиция текущей ноды относительно контейнера
            $positionOffset = 0;

            $partialNodes = $xpath->query('.//run', $token->getContainerNode());

            foreach ($partialNodes as $node) {

                $nodeLength = mb_strlen($node->nodeValue);
                $nodePosition = array(
                    self::LEFT => $positionOffset,
                    self::RIGHT => $positionOffset + $nodeLength
                );

                // Если отрезок токена имеет общие точки с нодой то вырезаем присутствующую часть токена
                if ($token->intersection($nodePosition[self::LEFT], $nodePosition[self::RIGHT]) === true) {

                    $tokenPosition = $token->getPosition();

                    // Нода
                    $textNode = $xpath->query('text', $node)->item(0);
                    $nodeValue = $textNode->nodeValue;
                    $textNode->nodeValue = '';

                    // Отступы от фрагмента токена, между ними вырезаем
                    $tokenPadding = $this->findTokenPadding(
                        $tokenPosition[self::LEFT],
                        $tokenPosition[self::RIGHT],
                        $nodePosition[self::LEFT],
                        $nodePosition[self::RIGHT]
                    );

                    // Текст до фрагмента токена
                    if ($tokenPadding[self::LEFT] > 0) {
                        $before = $template->createTextNode(mb_substr($nodeValue, 0, $tokenPadding[self::LEFT]));
                        $textNode->appendChild($before);
                    }

                    // Добавляем логику если мы в нужной ноде с левым краем токена
                    $between = $nodePosition[self::LEFT] <= $tokenPosition[self::LEFT] && $tokenPosition[self::LEFT] <= $nodePosition[self::RIGHT];

                    if ($token->isSolved() === false && $stripOnly === false && $between === true) {
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

                    // Текст после фрагмента токена
                    if ($tokenPadding[self::RIGHT] > 0) {
                        $after = $template->createTextNode(mb_substr($nodeValue, -$tokenPadding[self::RIGHT]));
                        $textNode->appendChild($after);
                    }
                }

                // Сдвигаем оффсет на изначальную длину ноды
                $positionOffset += $nodeLength;
            }
        }
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
        $provider = new WordNodeCollection($xpath, $this->brackets);

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