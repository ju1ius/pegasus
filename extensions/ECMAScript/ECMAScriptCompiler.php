<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Compiler\Extension\ECMAScript;

use ju1ius\Pegasus\Compiler\Compiler;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\OptimizationLevel;
use ju1ius\Pegasus\Grammar\Optimizer;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
class ECMAScriptCompiler extends Compiler
{
    private const PARSER_CLASSES = [
        self::PARSER_RECURSIVE_DESCENT => 'RecursiveDescentParser',
        self::PARSER_PACKRAT => 'PackratParser',
        self::PARSER_EXTENDED_PACKRAT => 'LeftRecursivePackratParser',
    ];

    public function getTwigExtensions(): array
    {
        return [];
    }

    public function getTemplateDirectories(): array
    {
        return [
            __DIR__ . '/templates',
        ];
    }

    /**
     * Returns the parser's FQCN for the given type.
     */
    public function getParserClass(string $parserType): string
    {
        return self::PARSER_CLASSES[$parserType];
    }

    public function getNodeVisitorClass(): string
    {
        return 'NodeTraverser';
    }

    protected function renderParser(array $args = []): string
    {
        return $this->renderTemplate('parser.twig', $args);
    }

    /**
     * @inheritDoc
     */
    protected function optimizeGrammar(Grammar $grammar, OptimizationLevel $level): Grammar
    {
        return Optimizer::optimize($grammar, $level);
    }
}
