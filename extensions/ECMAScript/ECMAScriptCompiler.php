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
    /**
     * @inheritDoc
     */
    public function getTwigExtensions()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getTemplateDirectories()
    {
        return [
            __DIR__ . '/templates',
        ];
    }

    /**
     * @inheritDoc
     */
    public function getParserClass()
    {
        return 'PackratParser';
    }

    /**
     * @inheritDoc
     */
    public function getExtendedParserClass()
    {
        return 'LeftRecursivePackratParser';
    }

    /**
     * @inheritDoc
     */
    public function getNodeVisitorClass()
    {
        return 'NodeTraverser';
    }

    /**
     * @inheritDoc
     */
    protected function renderParser($outputDirectory, array $args = [])
    {
        $output = $this->renderTemplate('parser.twig', $args);
        if ($outputDirectory === 'php://stdout') {
            $output_file = $outputDirectory;
        } else {
            $output_file = $outputDirectory . '/' . $args['class'] . '.js';
        }
        file_put_contents($output_file, $output);
    }

    /**
     * @inheritDoc
     */
    protected function optimizeGrammar(Grammar $grammar)
    {
        return Optimizer::optimize($grammar, Optimizer::LEVEL_2);
    }
}
