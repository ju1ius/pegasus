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
     * @param string $outputDirectory
     * @param array $args
     *
     * @return string
     */
    abstract protected function renderParser(string $outputDirectory, array $args = []): string;

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
     * @param string $outputDirectory
     * @param array $args
     */
    public function compileFile(string $path, string $outputDirectory = '', array $args = []): void
    {
        if (!$outputDirectory) {
            $outputDirectory = dirname($path);
        }
        if (empty($args['name'])) {
            $args['name'] = ucfirst(explode('.', basename($path))[0]);
        }
        $syntax = file_get_contents($path);
        $this->compileSyntax($syntax, $outputDirectory, $args);
    }

    /**
     * @param string $syntax
     * @param string $outputDirectory
     * @param array $args
     */
    public function compileSyntax(string $syntax, string $outputDirectory, array $args = []): void
    {
        $grammar = Grammar::fromSyntax($syntax);
        $name = $grammar->getName();
        if ($name) {
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
        $this->compileGrammar($grammar, $outputDirectory, $args);
    }

    /**
     * @param Grammar $grammar
     * @param string $outputDirectory
     * @param array $args
     */
    public function compileGrammar(Grammar $grammar, string $outputDirectory, array $args = []): void
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

        $this->renderParser($outputDirectory, $args);
    }

    protected function renderTemplate(string $tpl, array $args = []): string
    {
        $tpl = $this->twig->loadTemplate($tpl);

        return $tpl->render($args);
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
