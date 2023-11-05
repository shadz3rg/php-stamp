<?php

namespace PHPStamp\Processor;

class Tag
{
    /**
     * Tag data.
     *
     * @var array{position: int, length: int, textContent: string}
     */
    private array $summary;

    /**
     * Path to value in placeholder.
     *
     * @var array<string>
     */
    private array $path;

    /**
     * Parsed functions.
     *
     * @var array<array{function: string, arguments: string[]}>
     */
    private array $functions;

    /**
     * Creates a new Tag.
     *
     * @param array{position: int, length: int, textContent: string} $summary   tag data
     * @param array<string>                                          $path      path to value in placeholder
     * @param array<array{function: string, arguments: string[]}>    $functions parsed functions
     */
    public function __construct(array $summary, array $path, array $functions)
    {
        $this->summary = $summary;
        $this->path = $path;
        $this->functions = $functions;
    }

    /**
     * Placeholder position inside node content.
     * Left padding of string.
     */
    public function getPosition(): int
    {
        return $this->summary['position'];
    }

    /**
     * Length of tag with brackets.
     */
    public function getLength(): int
    {
        return $this->summary['length'];
    }

    /**
     * XPath presentation of value path.
     */
    public function getXmlPath(): string
    {
        return implode('/', $this->path);
    }

    /**
     * Last part of the path.
     */
    public function getRelativePath(): ?string
    {
        $output = end($this->path);
        if ($output === false) {
            return null;
        }

        return $output;
    }

    /**
     * Has function helper.
     */
    public function hasFunctions(): bool
    {
        return count($this->functions) !== 0;
    }

    /**
     * Get parsed functions.
     *
     * @return array<array{function: string, arguments: string[]}>
     */
    public function getFunctions(): array
    {
        return $this->functions; // TODO improve
    }

    /**
     * Get placeholder content with brackets.
     */
    public function getTextContent(): string
    {
        return $this->summary['textContent'];
    }
}
