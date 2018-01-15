<?php declare(strict_types=1);
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Compiler\Twig\Extension;

use ju1ius\Pegasus\Compiler\CompilationContext;
use ju1ius\Pegasus\Compiler\Compiler;
use ju1ius\Pegasus\Compiler\CompilerInterface;
use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Decorator\Quantifier;
use ju1ius\Pegasus\Expression\Terminal\Match;
use ju1ius\Pegasus\Utils\Str;

class PegasusTwigExtension extends \Twig_Extension
{
    /**
     * @var \Twig_Environment
     */
    private $environment;

    /**
     * @var CompilerInterface
     */
    private $compiler;

    public function __construct(CompilerInterface $compiler)
    {
        $this->compiler = $compiler;
    }

    public function initRuntime(\Twig_Environment $env)
    {
        parent::initRuntime($env);
        $this->environment = $env;
    }

    public function getName()
    {
        return 'pegasus';
    }

    public function getGlobals()
    {
        $globals = [];
        try {
            // if a template named macros exists,
            // make it available globally
            $macros = $this->environment->loadTemplate('macros.twig');
            $globals['macros'] = $macros;
        } catch (\Twig_Error_Loader $e) {}

        return $globals;
    }

    public function getTokenParsers()
    {
        return [];
    }

    public function getFunctions()
    {
        return [
            new \Twig_Function('render_expr', [$this, 'renderExpression']),
            new \Twig_Function('render_rule', [$this, 'renderRule']),
        ];
    }

    public function getFilters()
    {
        return [
            new \Twig_Filter('indent', [$this, 'indent']),
        ];
    }

    public function indent(string $text, int $level = 1, int $size = 4, ?callable $predicate = null): string
    {
        $prefix = str_repeat(' ', $size * $level);
        $predicate = $predicate ?: 'trim';
        $out = '';
        // split lines while keeping linebreak chars
        $lines = preg_split('/(?<=\n)/S', $text);
        foreach ($lines as $i => $line) {
            if ($i === 0) {
                // skip first line to preserve existing space before twig opening tag
                $out .= $line;
                continue;
            }
            $out .= $predicate($line) ? $prefix . $line : $line;
        }

        return $out;
    }

    public function renderRule(string $name, Expression $expr, CompilationContext $context): string
    {
        $tpl = $this->environment->loadTemplate('rule.twig');

        return $tpl->render([
            'expr' => $expr,
            'context' => $context->ofRule($name)
        ]);
    }

    public function renderExpression(Expression $expr, CompilationContext $context, array $args = []): string
    {
        $args = array_merge($args, [
            'expr' => $expr,
            'context' => $context
        ]);

        $template = $this->getTemplateForExpression($expr);
        $tpl = $this->environment->loadTemplate($template);

        return $tpl->render($args);
    }

    public function getTemplateForExpression(Expression $expr): string
    {
        if ($expr instanceof Quantifier) {
            return 'expression/Quantifier.twig';
        }
        if ($expr instanceof Match) {
            return 'expression/Match.twig';
        }

        return sprintf('expression/%s.twig', Str::className($expr));
    }
}
