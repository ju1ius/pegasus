<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Compiler\Extension\Php;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Utils\Str;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class PhpTwigExtension extends AbstractExtension
{
    public function __construct(
        private PhpCompiler $compiler
    ) {
    }

    public function getName(): string
    {
        return 'pegasus-php';
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('repr', [$this, 'repr']),
            new TwigFunction('repr_regexp', [$this, 'reprRegexp']),
            new TwigFunction('result_varname', [$this, 'getResultVariableName']),
            new TwigFunction('position_varname', [$this, 'getPositionVariableName']),
            new TwigFunction('expr_comment', [$this, 'getExpressionComment']),
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('escape_comment', [$this, 'escapeBlockComment']),
        ];
    }

    public function repr(mixed $value): string
    {
        if ($value === null) {
            return 'null';
        }
        if (\is_array($value)) {
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

    public function reprRegexp(string $pattern): string
    {
        $pattern = str_replace('\\\\', '\\\\\\\\', $pattern);

        return sprintf("'%s'", addcslashes($pattern, "'"));
    }

    public function escapeBlockComment(string $value): string
    {
        return str_replace('*/', '* /', $value);
    }

    public function getResultVariableName(Expression $expr): string
    {
        return sprintf('$result_%s', $expr->id);
    }

    public function getExpressionComment(Expression $expr, string $msg = ''): string
    {
        $class = Str::className($expr);
        $name = $expr->getName();
        return sprintf(
            '/* %s%s#%s%s: %s */',
            $name ? "{$name} = " : '',
            $class,
            $expr->id,
            $msg ? sprintf(' (%s)', $msg) : '',
            $this->escapeBlockComment((string)$expr)
        );
    }

    public function getPositionVariableName(Expression $expr): string
    {
        return sprintf('$pos_%s', $expr->id);
    }
}
