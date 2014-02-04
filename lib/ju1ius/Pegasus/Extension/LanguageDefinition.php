<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable 
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */



abstract class LanguageDefinition
{
    public function initRuntime()
    {

    }

    public function getTwigExtensions()
    {
        return [];
    }

    abstract public function getName();
    abstract public function getFileExtension();
    abstract public function getTemplateDirectories();
    abstract public function getParserClass();
    abstract public function getExtendedParserClass();
    abstract public function getNodeVisitorClass();
}
