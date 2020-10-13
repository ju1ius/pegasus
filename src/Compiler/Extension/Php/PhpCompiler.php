<?php declare(strict_types=1);
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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

    /**
     * @inheritdoc
     */
    public function getTemplateDirectories(): array
    {
        return [
            __DIR__ . '/templates',
        ];
    }

    /**
     * @inheritdoc
     */
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

    /**
     * @inheritdoc
     */
    public function getNodeVisitorClass(): string
    {
        return Transform::class;
    }

    /**
     * @inheritdoc
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
        return Optimizer::optimize($grammar, $optimizationLevel);
    }
}
