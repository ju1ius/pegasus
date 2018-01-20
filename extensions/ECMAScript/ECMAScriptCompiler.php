<?php
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Compiler\Extension\ECMAScript;

use ju1ius\Pegasus\Compiler\Compiler;
use ju1ius\Pegasus\Grammar;
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

    /**
     * @inheritDoc
     */
    public function getTwigExtensions(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getTemplateDirectories(): array
    {
        return [
            __DIR__ . '/templates',
        ];
    }

    /**
     * Returns the parser's FQCN for the given type.
     *
     * @param string $parserType
     * @return string
     */
    public function getParserClass(string $parserType): string
    {
        return self::PARSER_CLASSES[$parserType];
    }

    /**
     * @inheritDoc
     */
    public function getNodeVisitorClass(): string
    {
        return 'NodeTraverser';
    }

    /**
     * @inheritDoc
     */
    protected function renderParser(array $args = []): string
    {
        return $this->renderTemplate('parser.twig', $args);
    }

    /**
     * @inheritDoc
     */
    protected function optimizeGrammar(Grammar $grammar, int $optimizationLevel): Grammar
    {
        return Optimizer::optimize($grammar, Optimizer::LEVEL_2);
    }
}
