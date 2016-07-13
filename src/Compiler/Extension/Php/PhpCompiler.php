<?php
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
use ju1ius\Pegasus\Traverser\NamedNodeTraverser;

class PhpCompiler extends Compiler
{
    /**
     * @inheritdoc
     */
    public function getTemplateDirectories()
    {
        return [
            __DIR__ . '/templates',
        ];
    }

    /**
     * @inheritdoc
     */
    public function getTwigExtensions()
    {
        return [
            new PhpTwigExtension(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getParserClass()
    {
        return Packrat::class;
    }

    /**
     * @inheritdoc
     */
    public function getExtendedParserClass()
    {
        return LeftRecursivePackrat::class;
    }

    /**
     * @inheritdoc
     */
    public function getNodeVisitorClass()
    {
        return NamedNodeTraverser::class;
    }

    /**
     * @inheritdoc
     */
    protected function renderParser($outputDirectory, array $args = [])
    {
        $output = $this->renderTemplate('parser.twig', $args);
        if ($outputDirectory === 'php://stdout') {
            $output_file = $outputDirectory;
        } else {
            $output_file = $outputDirectory . '/' . $args['class'] . '.php';
        }
        file_put_contents($output_file, $output);
    }
}
