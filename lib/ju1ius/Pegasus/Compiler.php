<?php

namespace ju1ius\Pegasus;

use Twig_Loader_Filesystem;
use Twig_Environment;
use Twig_Extension_Debug;

use ju1ius\Pegasus\Twig\Extension\PegasusTwigExtension;
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
        foreach ($extension_dirs as $dir) {
            $this->lookupLanguageDefinitions($dir);
        }
    }

    public function compileSyntax($syntax, $args=[])
    {
        $grammar = Grammar::fromSyntax($syntax);
        return $this->compileGrammar($grammar, $args);
    }

    public function compileGrammar(Grammar $grammar, $args=[])
    {
        $def = $this->getLanguageDefinition();
        $args['grammar'] = $grammar;
        $args['base_class'] = $def['packrat_class'];

        $analysis = new Analysis($grammar);
        foreach ($grammar as $rule_name => $expr) {
            if ($analysis->isLeftRecursive($rule_name)) {
                $args['base_class'] = $def['lr_packrat_class'];
            }
        }

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
        $loader = new Twig_Loader_Filesystem($def['templates_dirs']);
        $this->twig = new Twig_Environment($loader, [
            'autoescape' => false,
            'debug' => true
        ]);
        $this->twig->addExtension(new PegasusTwigExtension);
        $this->twig->addExtension(new Twig_Extension_Debug);
    }

    public function addExtensionDirectory($dir)
    {
        $this->lookupLanguageDefinitions($dir);
    }

    public function addLanguageDefinition($path)
    {
        $def = require_once($path);
        $name = $def['name'];
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
            if ($finfo->isDir() && file_exists($path . '/language_definition.php')) {
                $this->addLanguageDefinition($path . '/language_definition.php');
            }   
        }
    }
}
