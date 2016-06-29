<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace ju1ius\Pegasus\Extension\Php;

use ju1ius\Pegasus\Compiler;


class PhpCompiler extends Compiler
{
    public function getTemplateDirectories()
    {
        return [
            __DIR__.'/templates'
        ];
    }

    public function getTwigExtensions()
    {
        return [
            new PhpTwigExtension()
        ];
    }

    public function getParserClass()
    {
        return 'ju1ius\Pegasus\Parser\Generated\Packrat';
    }

    public function getExtendedParserClass()
    {
        return 'ju1ius\Pegasus\Parser\Generated\LRPackrat';
    }

    public function getNodeVisitorClass()
    {
        return 'ju1ius\Pegasus\NodeVisitor';
    }

    protected function renderParser($outputDirectory, $args)
    {
        $output = $this->renderTemplate('parser.twig', $args);
        if ('php://stdout' === $outputDirectory) {
            $output_file = $outputDirectory;
        } else {
            $output_file = $outputDirectory . '/' . $args['class'] . '.php';
        }
        file_put_contents($output_file, $output);
    }
}
