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
            new \Twig_SimpleFunction('repr', [$this, 'repr']),
            new \Twig_SimpleFunction('repr_regexp', [$this, 'reprRegexp']),
            new \Twig_SimpleFunction('result_varname', [$this, 'getResultVariableName']),
            new \Twig_SimpleFunction('position_varname', [$this, 'getPositionVariableName']),
            new \Twig_SimpleFunction('expr_comment', [$this, 'getExpressionComment']),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('escape_comment', [$this, 'escapeBlockComment']),
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
}
