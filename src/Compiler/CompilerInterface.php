<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Compiler;

use ju1ius\Pegasus\Grammar;
use Twig\Environment;
use Twig\Extension\AbstractExtension;

interface CompilerInterface
{
    /**
     * Returns an array of paths to twig template directories.
     * @return string[]
     */
    public function getTemplateDirectories(): array;

    /**
     * Returns the parser's FQCN for the given type.
     */
    public function getParserClass(string $parserType): string;

    public function getNodeVisitorClass(): string;

    public function getTwigEnvironment(): Environment;

    /**
     * @return AbstractExtension[]
     */
    public function getTwigExtensions(): array;

    public function compileFile(string $path, array $args = []): string;

    public function compileSyntax(string $syntax, array $args = []): string;

    public function compileGrammar(Grammar $grammar, array $args = []): string;
}
