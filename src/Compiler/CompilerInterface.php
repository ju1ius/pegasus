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
     * Returns the packrat parser's FQCN.
     *
     * @return string
     */
    public function getParserClass(): string;

    /**
     * Returns the left-recursive packrat parser's FQCN.
     *
     * @return string
     */
    public function getExtendedParserClass(): string;

    public function getNodeVisitorClass(): string;

    public function getTwigEnvironment(): \Twig_Environment;

    /**
     * @return \Twig_Extension[]
     */
    public function getTwigExtensions(): array;

    /**
     * @param string $path
     * @param string $outputDirectory
     * @param array $args
     */
    public function compileFile(string $path, string $outputDirectory = '', array $args = []): void;

    /**
     * @param string $syntax
     * @param string $outputDirectory
     * @param array $args
     */
    public function compileSyntax(string $syntax, string $outputDirectory, array $args = []): void;

    /**
     * @param Grammar $grammar
     * @param string $outputDirectory
     * @param array $args
     */
    public function compileGrammar(Grammar $grammar, string $outputDirectory, array $args = []): void;
}