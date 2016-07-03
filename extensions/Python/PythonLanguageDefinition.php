<?php

use ju1ius\Pegasus\Extension\LanguageDefinition;


class PythonLanguageDefinition extends LanguageDefinition
{
    public function getTwigExtensions()
    {
        return [];
    }

    public function getName()
    {
        return 'python';
    }

    public function getFileExtension()
    {
        return 'py';
    }

    public function getTemplateDirectories()
    {
        return [__DIR__.'/templates'];
    }

    public function getParserClass()
    {
        return 'pegasus.parsers.Packrat';
    }

    public function getExtendedParserClass()
    {
        return 'pegasus.parsers.LeftRecursivePackrat';
    }

    public function getNodeVisitorClass()
    {
        return 'pegasus.visitors.DepthFirstNodeTraverser';
    }
}
