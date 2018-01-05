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
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\Optimizer;
use ju1ius\Pegasus\CST\Transform;

class PhpCompiler extends Compiler
{
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
            new PhpTwigExtension(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getParserClass(): string
    {
        return Packrat::class;
    }

    /**
     * @inheritdoc
     */
    public function getExtendedParserClass(): string
    {
        return LeftRecursivePackrat::class;
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
    protected function renderParser(string $outputDirectory, array $args = []): string
    {
        $output = $this->renderTemplate('parser.twig', $args);
        if ($outputDirectory === 'php://stdout') {
            $output_file = $outputDirectory;
        } else {
            $output_file = $outputDirectory . '/' . $args['class'] . '.php';
        }
        file_put_contents($output_file, $output);
    }

    /**
     * @inheritDoc
     */
    protected function optimizeGrammar(Grammar $grammar): Grammar
    {
        return Optimizer::optimize($grammar, Optimizer::LEVEL_2);
    }
}
