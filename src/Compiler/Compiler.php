<?php declare(strict_types=1);
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Compiler;

use ju1ius\Pegasus\Compiler\Twig\Extension\PegasusTwigExtension;
use ju1ius\Pegasus\Grammar;

abstract class Compiler implements CompilerInterface
{
    /**
     * @var \Twig_Environment
     */
    protected $twig = null;

    public function __construct()
    {
        $this->setupTwigEnvironment();
    }

    /**
     * @param array $args
     *
     * @return string
     */
    abstract protected function renderParser(array $args = []): string;

    /**
     * Override this to add optimizations
     *
     * @param Grammar $grammar
     *
     * @return Grammar
     */
    abstract protected function optimizeGrammar(Grammar $grammar): Grammar;

    public function getTwigEnvironment(): \Twig_Environment
    {
        return $this->twig;
    }

    /**
     * @return \Twig_Extension[]
     */
    public function getTwigExtensions(): array
    {
        return [];
    }

    /**
     * @param string $path
     * @param array $args
     * @return string
     */
    public function compileFile(string $path, array $args = []): string
    {
        if (empty($args['name'])) {
            $args['name'] = ucfirst(explode('.', basename($path))[0]);
        }
        $syntax = file_get_contents($path);

        return $this->compileSyntax($syntax, $args);
    }

    /**
     * @param string $syntax
     * @param array $args
     * @return string
     * @throws Grammar\Exception\MissingTraitAlias
     */
    public function compileSyntax(string $syntax, array $args = []): string
    {
        $grammar = Grammar::fromSyntax($syntax);
        if (empty($args['class'])) {
            if ($name = $grammar->getName()) {
                $args['class'] = ucfirst($name);
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
        }

        return $this->compileGrammar($grammar, $args);
    }

    /**
     * @param Grammar $grammar
     * @param array $args
     * @return string
     */
    public function compileGrammar(Grammar $grammar, array $args = []): string
    {
        $args['base_class'] = $this->getParserClass();
        $grammar = $this->optimizeGrammar($grammar);
        $context = CompilationContext::of($grammar);
        // analyse grammar
        $analysis = $context->getAnalysis();
        // find the appropriate parser class
        foreach ($grammar as $ruleName => $expr) {
            if ($analysis->isLeftRecursive($ruleName)) {
                $args['base_class'] = $this->getExtendedParserClass();
                break;
            }
        }
        $args['context'] = $context;

        return $this->renderParser($args);
    }

    protected function renderTemplate(string $tpl, array $args = []): string
    {
        return $this->twig->render($tpl, $args);
    }

    protected function setupTwigEnvironment(): void
    {
        $loader = new \Twig_Loader_Filesystem($this->getTemplateDirectories());
        $this->twig = new \Twig_Environment($loader, [
            'autoescape' => false,
            'debug' => true,
            'strict_variables' => true,
        ]);
        $extensions = [
            new \Twig_Extension_Debug,
            new PegasusTwigExtension($this),
        ];
        $extensions = array_merge($extensions, $this->getTwigExtensions());
        foreach ($extensions as $ext) {
            $this->twig->addExtension($ext);
        }
    }
}
