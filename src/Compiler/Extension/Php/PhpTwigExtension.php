<?php declare(strict_types=1);
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Compiler\Extension\Php;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Utils\Str;

class PhpTwigExtension extends \Twig_Extension
{
    /**
     * @var PhpCompiler
     */
    private $compiler;

    public function __construct(PhpCompiler $compiler)
    {
        $this->compiler = $compiler;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'pegasus-php';
    }

    /**
     * @inheritdoc
     */
    public function getFunctions()
    {
        return [
            new \Twig_Function('repr', [$this, 'repr']),
            new \Twig_Function('repr_regexp', [$this, 'reprRegexp']),
            new \Twig_Function('result_varname', [$this, 'getResultVariableName']),
            new \Twig_Function('position_varname', [$this, 'getPositionVariableName']),
            new \Twig_Function('expr_comment', [$this, 'getExpressionComment']),
            new \Twig_Function('failure', [$this, 'renderFailure']),
            new \Twig_Function('start_non_capturing', [$this, 'renderStartNonCapturing']),
            new \Twig_Function('end_non_capturing', [$this, 'renderEndNonCapturing']),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getFilters()
    {
        return [
            new \Twig_Filter('escape_comment', [$this, 'escapeBlockComment']),
        ];
    }

    /**
     * @param mixed $value
     *
     * @return string
     */
    public function repr($value)
    {
        if ($value === null) {
            return 'null';
        }
        if (is_array($value)) {
            $out = '[';
            $first = true;
            foreach ($value as $k => $v) {
                if (!$first) {
                    $out .= ', ';
                }
                $first = false;
                $out .= $this->repr($k) . ' => ' . $this->repr($v);
            }

            return $out . ']';
        }
        if ($value instanceof Expression) {
            $value = (string) $value;
        }

        return var_export($value, true);
    }

    /**
     * @param string $pattern
     *
     * @return string
     */
    public function reprRegexp($pattern)
    {
        $pattern = str_replace('\\\\', '\\\\\\\\', $pattern);

        return sprintf("'%s'", addcslashes($pattern, "'"));
    }

    /**
     * @param string $value
     *
     * @return string
     */
    public function escapeBlockComment($value)
    {
        return str_replace('*/', '* /', $value);
    }

    /**
     * @param Expression $expr
     *
     * @return string
     */
    public function getResultVariableName(Expression $expr)
    {
        return sprintf('$result_%s', $expr->id);
    }

    public function getExpressionComment(Expression $expr, $msg = '')
    {
        $class = Str::className($expr);

        return sprintf(
            '/* %s#%s%s: %s */',
            $class,
            $expr->id,
            $msg ? sprintf(' (%s)', $msg) : '',
            $this->escapeBlockComment((string)$expr)
        );
    }

    /**
     * @param Expression $expr
     *
     * @return string
     */
    public function getPositionVariableName(Expression $expr)
    {
        return sprintf('$pos_%s', $expr->id);
    }

    public function renderFailure(string $rule, Expression $expr, string $pos = '$this->pos')
    {
        return $this->renderHelper('failure', [
            'rule' => $rule,
            'expr' => $expr,
            'pos' => $pos,
        ]);
    }

    public function renderStartNonCapturing(Expression $expr)
    {
        return $this->renderHelper('start_non_capturing', ['expr' => $expr]);
    }

    public function renderEndNonCapturing(Expression $expr)
    {
        return $this->renderHelper('end_non_capturing', ['expr' => $expr]);
    }

    private function renderHelper(string $helper, array $args)
    {
        $template = sprintf('helper/%s.twig', $helper);
        return rtrim($this->compiler->renderTemplate($template, $args));
    }
}
