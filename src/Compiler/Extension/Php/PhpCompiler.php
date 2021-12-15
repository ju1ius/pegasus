<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Compiler\Extension\Php;

use ju1ius\Pegasus\Compiler\Compiler;
use ju1ius\Pegasus\Compiler\Extension\Php\Runtime\LeftRecursivePackrat;
use ju1ius\Pegasus\Compiler\Extension\Php\Runtime\Packrat;
use ju1ius\Pegasus\Compiler\Extension\Php\Runtime\RecursiveDescent;
use ju1ius\Pegasus\CST\Transform;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\Optimizer;

class PhpCompiler extends Compiler
{
    private const PARSER_CLASSES = [
        self::PARSER_RECURSIVE_DESCENT => RecursiveDescent::class,
        self::PARSER_PACKRAT => Packrat::class,
        self::PARSER_EXTENDED_PACKRAT => LeftRecursivePackrat::class,
    ];

    public function getTemplateDirectories(): array
    {
        return [
            __DIR__ . '/templates',
        ];
    }

    public function getTwigExtensions(): array
    {
        return [
            new PhpTwigExtension($this),
        ];
    }

    public function getParserClass(string $parserType): string
    {
        return self::PARSER_CLASSES[$parserType];
    }

    public function getNodeVisitorClass(): string
    {
        return Transform::class;
    }

    protected function renderParser(array $args = []): string
    {
        return $this->renderTemplate('parser.twig', $args);
    }

    protected function optimizeGrammar(Grammar $grammar, int $optimizationLevel): Grammar
    {
        return Optimizer::optimize($grammar, $optimizationLevel);
    }
}
