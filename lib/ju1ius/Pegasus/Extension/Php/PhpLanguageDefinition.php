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

use ju1ius\Pegasus\Extension\LanguageDefinition;


class PhpLanguageDefinition extends LanguageDefinition
{
    public function getTwigExtensions()
    {
        return [];
    }

    public function getName()
    {
        return 'php';
    }

    public function getFileExtension()
    {
        return 'php';
    }

    public function getTemplateDirectories()
    {
        return [__DIR__.'/templates'];
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
}
