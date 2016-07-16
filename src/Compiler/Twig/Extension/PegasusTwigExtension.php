<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Compiler\Twig\Extension;

use ju1ius\Pegasus\Compiler\Compiler;
use ju1ius\Pegasus\Compiler\Twig\Context;
use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Compiler\Twig\DataCollector;
use ju1ius\Pegasus\Compiler\Twig\TokenParser\CollectorTokenParser;
use Twig_Environment;
use Twig_Error_Loader;
use Twig_Extension;
use Twig_SimpleFilter;
use Twig_SimpleFunction;

class PegasusTwigExtension extends Twig_Extension
{
    /**
     * @var Twig_Environment
     */
    private $environment;

    /**
     * @var Compiler
     */
    private $compiler;

    /**
     * @var DataCollector
     */
    private $collector;

    public function __construct(Compiler $compiler)
    {
        $this->compiler = $compiler;
    }

    public function initRuntime(Twig_Environment $env)
    {
        parent::initRuntime($env);
        $this->environment = $env;
        $this->collector = new DataCollector();
    }

    public function getName()
    {
        return 'pegasus';
    }

    public function getGlobals()
    {
        $globals = [
            'data_collector' => $this->collector,
            'context' => new Context()
        ];
        try {
            // if a template named macros exists,
            // make it available globally
            $macros = $this->environment->loadTemplate('macros.twig');
            $globals['macros'] = $macros;
        } catch (Twig_Error_Loader $e) {}

        return $globals;
    }

    public function getTokenParsers()
    {
        return [
            new CollectorTokenParser(),
        ];
    }

    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('render_expr', [$this, 'renderExpression']),
            new Twig_SimpleFunction('render_rule', [$this, 'renderRule']),
            new Twig_SimpleFunction('retrieve', [$this->collector, 'retrieve']),
        ];
    }

    public function getFilters()
    {
        return [
            new Twig_SimpleFilter('indent', [$this, 'indent']),
        ];
    }

    public function indent($text, $level = 1, $size = 4, callable $predicate = null)
    {
        $prefix = str_repeat(' ', $size * $level);
        $predicate = $predicate ?: 'trim';
        $out = '';
        // split lines while keeping linebreak chars
        $lines = preg_split('/(?<=\r\n|\n|\r)/S', $text);
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

    public function renderRule($name, Expression $expr)
    {
        $tpl = $this->environment->loadTemplate('rule.twig');
        $this->environment->addGlobal('current_rule', $name);

        return $tpl->render([
            'expr' => $expr,
            'current_rule' => $name
        ]);
    }

    public function renderExpression(Expression $expr, array $args = [])
    {
        $args = array_merge($args, ['expr' => $expr]);
        if (!isset($args['capturing'])) {
            $args['capturing'] = $expr->isCapturingDecidable() && $expr->isCapturing();
        }
        $template = $this->getTemplateForExpression($expr);
        $tpl = $this->environment->loadTemplate($template);

        return $tpl->render($args);
    }

    public function getTemplateForExpression(Expression $expr)
    {
        $class = strtolower(str_replace('ju1ius\Pegasus\Expression\\', '', get_class($expr)));
        switch ($class) {
            case 'zeroormore':
            case 'oneormore':
            case 'optional':
                return 'expression/quantifier.twig';
            default:
                return "expression/{$class}.twig";
        }
    }
}
