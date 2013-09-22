<?php
namespace OfficeML;

class Processor
{
    const NS = 'http://www.w3.org/1999/XSL/Transform';
    const LEFT = 0;
    const RIGHT = 1;

    private $brackets;

    function __construct(array $brackets = array('[[', ']]')) {
        // TODO Improve
        if (count($brackets) !== 2 || array_values($brackets) !== $brackets) {
            throw new Exception\ArgumentsException('Brackets in wrong format.');
        }
        $this->brackets = $brackets;
    }

    public function compile($compileInto, \DOMDocument $doc)
    {
        // Template declaration IE cached document
        $stylesheet = $doc->createElementNS(self::NS, 'xsl:stylesheet');

        $output = $doc->createElementNS(self::NS, 'xsl:output');
        $output->setAttribute('method', 'xml');
        $output->setAttribute('encoding', 'UTF-8');
        //$output->setAttribute('omit-xml-declaration', 'yes'); TODO Optional output?
        $stylesheet->appendChild($output);

        $template = $doc->createElementNS(self::NS, 'xsl:template');
        $template->setAttribute('match', '//tokens');
        $template->appendChild($doc->documentElement);

        $stylesheet->appendChild($template);

        /*
        $call = $doc->createElementNS(self::NS, 'xsl:call-template');
        $call->setAttribute('name', 'main');
        $stylesheet->appendChild($call);
        */

        $stylesheet->setAttribute('version', '1.0');
        $doc->appendChild($stylesheet);

        // Operation TODO Код дублируется из геттокенс, убогий подход
        $xpath = new \DOMXPath($doc);
        $query = sprintf('//root/*[contains(., "%s")][contains(., "%s")]',
            $this->brackets[self::LEFT],
            $this->brackets[self::RIGHT]
        );

        $nodes = $xpath->query($query);
        if ($nodes->length === 0) {
            throw new Exception\TokenException('Tokens not found.');
        }

        $lexer = new Lexer($this->brackets);

        for ($i = 0; $i < $nodes->length; $i++) {

            // Paragraph Node
            $node = $nodes->item($i);
            $lexer->setInput(utf8_decode($node->textContent));

            $lengthCache = 0;
            $recycled = array();

            while ($token = $lexer->peek()) {
                $token[self::LEFT] = $token['position'] - $lengthCache;
                $token[self::RIGHT] = $token[self::LEFT] + mb_strlen($token['value']);

                $token['variable'] = Helper::strip($token['value'], $this->brackets);

                // Начинается нода
                $positionOffset = 0;

                for ($c = 0; $c < $node->childNodes->length; $c++) {
                    $partNode = $node->childNodes->item($c);
                    $partLength = mb_strlen($partNode->nodeValue);

                    $position = array();
                    $position[self::LEFT] = $positionOffset;
                    $position[self::RIGHT] = $position[self::LEFT] + $partLength;

                    // Контент тэга со скобками
                    $isLeftInBound = ($token[self::LEFT] <= $position[self::LEFT] && $position[self::LEFT] <= $token[self::RIGHT]);
                    $isRightInBound = ($token[self::LEFT] <= $position[self::RIGHT] && $position[self::RIGHT] <= $token[self::RIGHT]);;

                    if ($isLeftInBound === true || $isRightInBound === true) {
                        $textNodes = $xpath->query('text', $partNode);
                        $textNode = $textNodes->item(0);

                        $start = $token[self::RIGHT] - $position[self::LEFT];
                        if ($position[self::RIGHT] <= $token[self::RIGHT]) {
                            $start = 0;
                        }

                        $length = 0;
                        if ($position[self::LEFT] <= $token[self::LEFT]) {
                            $length = $token[self::LEFT] - $positionOffset;

                            $placeholder = $doc->createElementNS(self::NS, 'xsl:value-of');
                            $placeholder->setAttribute('select', '//tokens/city');
                            $textNode->appendChild($placeholder);

                        } elseif ($position[self::RIGHT] >= $token[self::RIGHT]) {
                            $length = $position[self::RIGHT] - $token[self::RIGHT];
                        }

                        $textNode->nodeValue = mb_substr($backupValue = $textNode->nodeValue, $start, $length);

                        // TODO Надо ли удалять протухшие ноды?
                        if ($backupValue !== $textNode->nodeValue && $textNode->nodeValue !== '' && false) {
                            $recycled[] = $partNode;
                        }

                        if ($position[self::LEFT] <= $token[self::LEFT]) {
                            $placeholder = $doc->createElementNS(self::NS, 'xsl:value-of');
                            $placeholder->setAttribute('select', '//tokens/' . $token['variable']);
                            $textNode->appendChild($placeholder);
                        }
                    }

                    $positionOffset += $partLength;
                }

                $lengthCache += mb_strlen($token['value']);
            }

            foreach ($recycled as $recycledNode) {
                $node->removeChild($recycledNode);
            }
        }

        // На этом месте шаблон готов и его можно сохранять
        if ($this->saveCompiled($compileInto, $doc) === false) {
            throw new OpenXMLException('Compiled stylesheet not saved.');
        }
    }

    public function getTokens(\DOMDocument $doc) {
        $xpath = new \DOMXPath($doc);
        $query = sprintf('//root/*[contains(., "%s")][contains(., "%s")]',
            $this->brackets[self::LEFT],
            $this->brackets[self::RIGHT]
        );

        $nodes = $xpath->query($query);
        if ($nodes->length === 0) {
            throw new Exception\TokenException('Tokens not found.');
        }

        $lexer = new Lexer($this->brackets);

        $tokens = array();
        for ($i = 0; $i < $nodes->length; $i++) {

            // Paragraph
            $node = $nodes->item($i);
            $lexer->setInput($node->nodeValue);

            while ($token = $lexer->peek()) {

                //TODO Неправильный путь, для этого прийдется пересчитывать позицию
                //$token['value'] = Helper::strip($token['value'], $this->brackets);
                $tokens[] = $token;
            }
        }

        return $tokens;
    }

    private function saveCompiled($compileInto, \DOMDocument $template)
    {
        return $template->save($compileInto);
    }
}