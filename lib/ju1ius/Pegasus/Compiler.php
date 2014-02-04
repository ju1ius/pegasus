<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable 
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace ju1ius\Pegasus;

use Twig_Loader_Filesystem;
use Twig_Environment;
use Twig_Extension_Debug;

use ju1ius\Pegasus\Twig\Extension\PegasusTwigExtension;
use ju1ius\Pegasus\Extension\LanguageDefinition;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\Analysis;


class Compiler
{
    protected $definitions = [];
    protected $extension_dirs = [];
    protected $language = 'php';
    protected $twig = null;

    public function __construct($extension_dirs = [])
    {
        // Core Php extension is always enabled
        $extension_dirs = array_merge([__DIR__.'/Extension'], $extension_dirs);

        foreach ($extension_dirs as $dir) {
            $this->lookupLanguageDefinitions($dir);
        }
    }

    public function compileSyntax($syntax, $args=[])
    {
        $grammar = Grammar::fromSyntax($syntax);
        $name = $grammar->getName();
        if ($name) {
            $args['class'] = $name;
        } else {
            if (empty($args['name'])) {
                throw new \InvalidArgumentException(
                    'You must provide a name for the grammar, either with the %name directive or by passing a "name" parameter to the arguments array.'
                );
            }
            $args['class'] = $args['name'];
            unset($args['name']);
        }
        return $this->compileGrammar($grammar, $args);
    }

    public function compileGrammar(Grammar $grammar, $args=[])
    {
        $def = $this->getLanguageDefinition();
        $args['grammar'] = $grammar;
        $args['base_class'] = $def->getParserClass();

        // analyse grammar
        $analysis = new Analysis($grammar);
        // find the appropiate parser class
        foreach ($grammar as $rule_name => $expr) {
            if ($analysis->isLeftRecursive($rule_name)) {
                $args['base_class'] = $def->getExtendedParserClass();
                break;
            }
        }
        // TODO: optionally add optimizations

        return $this->renderTemplate('parser.twig', $args);
    }
    
    public function getTwigEnvironment()
    {
        return $this->twig;
    }

    public function setLanguage($name)
    {
        $this->language = $name;
        $def = $this->getLanguageDefinition($name);
        $loader = new Twig_Loader_Filesystem($def->getTemplateDirectories());
        $this->twig = new Twig_Environment($loader, [
            'autoescape' => false,
            'debug' => true
        ]);
        $this->twig->addExtension(new Twig_Extension_Debug);
        $this->twig->addExtension(new PegasusTwigExtension);
        foreach ($def->getTwigExtensions() as $ext) {
            $this->twig->addExtension($ext);
        }
    }

    public function addExtensionDirectory($dir)
    {
        $this->lookupLanguageDefinitions($dir);
    }

    public function addLanguageDefinition(LanguageDefinition $def)
    {
        $name = $def->getName();
        $this->definitions[$name] = $def;
    }

    public function getLanguageDefinition($name=null)
    {
        $name = $name ?: $this->language;
        if (!isset($this->definitions[$name])) {
            throw new \InvalidArgumentException(
                "Unknown language '$name'. Did you forget to call Compiler::addExtensionDirectory() ?"
            );
        }

        return $this->definitions[$name];
    }

    protected function renderTemplate($tpl, $args=[])
    {
        $tpl = $this->twig->loadTemplate($tpl);
        return $tpl->render($args);
    }

    protected function lookupLanguageDefinitions($dir)
    {
        foreach (new \FilesystemIterator($dir) as $path => $finfo) {
            if ($finfo->isDir() && file_exists($path.'/definition.php')) {
                $def = include_once($path.'/definition.php');
                $this->addLanguageDefinition($def);
            }   
        }
    }
}
