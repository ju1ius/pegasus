<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Compiler;

use ju1ius\Pegasus\Grammar;


interface CompilerInterface
{
    /**
     * Returns an array of paths to twig template directories.
     *
     * @return string[]
     */
    public function getTemplateDirectories(): array;

    /**
     * Returns the parser's FQCN for the given type.
     *
     * @param string $parserType
     * @return string
     */
    public function getParserClass(string $parserType): string;

    public function getNodeVisitorClass(): string;

    public function getTwigEnvironment(): \Twig_Environment;

    /**
     * @return \Twig_Extension[]
     */
    public function getTwigExtensions(): array;

    /**
     * @param string $path
     * @param array $args
     * @return string
     */
    public function compileFile(string $path, array $args = []): string;

    /**
     * @param string $syntax
     * @param array $args
     * @return string
     */
    public function compileSyntax(string $syntax, array $args = []): string;

    /**
     * @param Grammar $grammar
     * @param array $args
     * @return string
     */
    public function compileGrammar(Grammar $grammar, array $args = []): string;
}
