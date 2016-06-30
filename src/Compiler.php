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

use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\Analysis;
use ju1ius\Pegasus\Twig\Extension\PegasusTwigExtension;
use Twig_Environment;
use Twig_Extension_Debug;
use Twig_Loader_Filesystem;

abstract class Compiler
{
    /**
     * @var Twig_Environment
     */
    protected $twig = null;

    public function __construct()
    {
        $this->setupTwigEnvironment();
    }

    abstract public function getTemplateDirectories();

    abstract public function getParserClass();

    abstract public function getExtendedParserClass();

    abstract public function getNodeVisitorClass();

    abstract protected function renderParser($outputDirectory, $args);

    /**
     * @return Twig_Environment
     */
    public function getTwigEnvironment()
    {
        return $this->twig;
    }

    /**
     * @return array
     */
    public function getTwigExtensions()
    {
        return [];
    }

    /**
     * @param string $path
     * @param string $outputDirectory
     * @param array  $args
     */
    public function compileFile($path, $outputDirectory = '', $args = [])
    {
        if (!$outputDirectory) {
            $outputDirectory = dirname($path);
        }
        if (empty($args['name'])) {
            $args['name'] = explode('.', basename($path))[0];
        }
        $syntax = file_get_contents($path);
        $this->compileSyntax($syntax, $outputDirectory, $args);
    }

    /**
     * @param string $syntax
     * @param string $outputDirectory
     * @param array  $args
     */
    public function compileSyntax($syntax, $outputDirectory, $args = [])
    {
        $grammar = Grammar::fromSyntax($syntax);
        $name = $grammar->getName();
        if ($name) {
            $args['class'] = $name;
        } else {
            if (empty($args['name'])) {
                throw new \InvalidArgumentException(
                    'You must provide a name for the grammar'
                    . ', either with the %name directive or by passing a "name" parameter to the arguments array.'
                );
            }
            $args['class'] = $args['name'];
            unset($args['name']);
        }
        $this->compileGrammar($grammar, $outputDirectory, $args);
    }

    /**
     * @param Grammar $grammar
     * @param string  $outputDirectory
     * @param array   $args
     */
    public function compileGrammar(Grammar $grammar, $outputDirectory, $args = [])
    {
        $args['grammar'] = $grammar;
        $args['base_class'] = $this->getParserClass();

        // analyse grammar
        $analysis = new Analysis($grammar);
        // find the appropiate parser class
        foreach ($grammar as $rule_name => $expr) {
            if ($analysis->isLeftRecursive($rule_name)) {
                $args['base_class'] = $this->getExtendedParserClass();
                break;
            }
        }
        $this->optimizeGrammar($grammar, $analysis);
        $this->renderParser($outputDirectory, $args);
    }

    /**
     * @param Expression $expr
     *
     * @return string
     */
    public function renderExpression(Expression $expr)
    {
        $tpl_name = self::getExpressionTemplate($expr);
        $args = ['expr' => $expr];

        return $this->renderTemplate($tpl_name, $args);
    }

    protected function renderTemplate($tpl, $args = [])
    {
        $tpl = $this->twig->loadTemplate($tpl);

        return $tpl->render($args);
    }

    protected static function getExpressionTemplate($expr)
    {
        $class = strtolower(str_replace('ju1ius\Pegasus\Expression\\', '', get_class($expr)));
        switch ($class) {
            case 'zeroormore':
            case 'oneormore':
            case 'optional':
                return 'quantifier.twig';
            default:
                return "$class.twig";
        }
    }

    protected function setupTwigEnvironment()
    {
        $loader = new Twig_Loader_Filesystem($this->getTemplateDirectories());
    $this->twig = new Twig_Environment($loader, [
            'autoescape' => false,
            'debug' => true,
        ]);
        $extensions = [
            new Twig_Extension_Debug,
            new PegasusTwigExtension($this),
        ];
        $extensions = array_merge($extensions, $this->getTwigExtensions());
        foreach ($extensions as $ext) {
            $this->twig->addExtension($ext);
        }
    }

    /**
     * Override this to add optimizations
     *
     * @param GrammarInterface $grammar
     * @param Analysis         $analysis
     */
    protected function optimizeGrammar(GrammarInterface $grammar, Analysis $analysis)
    {
        //
    }
}
