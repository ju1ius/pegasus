<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Compiler;

use ju1ius\Pegasus\Compiler\Twig\Extension\PegasusTwigExtension;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\OptimizationLevel;
use ju1ius\Pegasus\Grammar\Optimizer;
use ju1ius\Pegasus\GrammarFactory;
use Symfony\Component\Filesystem\Path;
use Twig\Environment;
use Twig\Error\Error as TwigError;
use Twig\Extension\AbstractExtension;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;

abstract class Compiler implements CompilerInterface
{
    const PARSER_RECURSIVE_DESCENT = 'recursive_descent';
    const PARSER_PACKRAT = 'packrat';
    const PARSER_EXTENDED_PACKRAT = 'extended_packrat';

    protected ?Environment $twig = null;

    public function __construct()
    {
        $this->setupTwigEnvironment();
    }

    abstract protected function renderParser(array $args = []): string;

    /**
     * Override this to add optimizations
     */
    abstract protected function optimizeGrammar(Grammar $grammar, OptimizationLevel $level): Grammar;

    public function getTwigEnvironment(): Environment
    {
        return $this->twig;
    }

    /**
     * @return AbstractExtension[]
     */
    public function getTwigExtensions(): array
    {
        return [];
    }

    public function useCache(string|false $cache): void
    {
        $this->twig->setCache($cache);
    }

    /**
     * @throws Grammar\Exception\MissingTraitAlias
     */
    public function compileFile(string $path, array $args = []): string
    {
        $grammar = GrammarFactory::fromFile($path, OptimizationLevel::NONE);
        if (empty($args['class'])) {
            $name = $grammar->getName() ?? $args['name'] ?? ucfirst(explode('.', basename($path))[0]);
            $args['class'] = sprintf('%sParser', ucfirst($name));
            $args['name'] = $name;
        }

        return $this->compileGrammar($grammar, $args);
    }

    /**
     * @throws Grammar\Exception\MissingTraitAlias
     */
    public function compileSyntax(string $syntax, array $args = []): string
    {
        $grammar = GrammarFactory::fromSyntax($syntax, null, OptimizationLevel::NONE);
        $name = $grammar->getName() ?? $args['name'] ?? null;
        if (empty($args['class'])) {
            if (!$name) {
                throw new \InvalidArgumentException(
                    'You must provide a name for the grammar'
                    . ', either with the @grammar directive or by passing a "name" parameter to the arguments array.'
                );
            }
            $args['class'] = sprintf('%sParser', ucfirst($name));
            unset($args['name']);
        }

        return $this->compileGrammar($grammar, $args);
    }

    /**
     * @return string
     */
    public function compileGrammar(Grammar $grammar, array $args = []): string
    {
        $optimizationLevel = $args['optimization-level'] ?? OptimizationLevel::LEVEL_1;
        $grammar = $this->optimizeGrammar($grammar, $optimizationLevel);
        $context = CompilationContext::of($grammar);
        $parserType = $this->guessParserType($context, $args);

        if ($parent = $grammar->getParent()) {
            $parserClass = sprintf('%sParser', $parent->getName());
        } else {
            $parserClass = $this->getParserClass($parserType);
        }

        return $this->renderParser([
            'class' => $args['class'],
            'base_class' => $parserClass,
            'parser_type' => $parserType,
            'context' => $context,
            'use_cache' => $parserType === self::PARSER_PACKRAT,
            'use_apply' => $parserType === self::PARSER_EXTENDED_PACKRAT,
        ]);
    }

    /**
     * @throws TwigError
     */
    public function renderTemplate(string $tpl, array $args = []): string
    {
        return $this->twig->render($tpl, $args);
    }

    protected function setupTwigEnvironment(): void
    {
        $loader = new FilesystemLoader($this->getTemplateDirectories());
        $this->twig = new Environment($loader, [
            'autoescape' => false,
            'debug' => true,
            'strict_variables' => true,
        ]);
        $extensions = [
            new DebugExtension(),
            new PegasusTwigExtension($this),
        ];
        $extensions = array_merge($extensions, $this->getTwigExtensions());
        foreach ($extensions as $ext) {
            $this->twig->addExtension($ext);
        }
    }

    protected function guessParserType(CompilationContext $context, array $args): string
    {
        $grammar = $context->getGrammar();
        $analysis = $context->getAnalysis();
        $noCache = $args['no-cache'] ?? false;
        $parserType = $noCache ? self::PARSER_RECURSIVE_DESCENT : self::PARSER_PACKRAT;
        // find the appropriate parser class
        foreach ($grammar as $ruleName => $expr) {
            if ($analysis->isLeftRecursive($ruleName)) {
                return self::PARSER_EXTENDED_PACKRAT;
            }
        }

        return $parserType;
    }
}
