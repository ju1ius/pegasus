<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Grammar\Optimization;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Decorator\Quantifier;
use ju1ius\Pegasus\Expression\Terminal\EOF;
use ju1ius\Pegasus\Expression\Terminal\GroupMatch;
use ju1ius\Pegasus\Expression\Terminal\Literal;
use ju1ius\Pegasus\Expression\Terminal\AbstractRegExp;
use ju1ius\Pegasus\Utils\Str;

class PCREManipulator implements RegExpManipulator
{
    protected const MERGEABLE_FLAGS = ['i', 'm', 's', 'x', 'U', 'X', 'J'];

    public function __construct(
        protected string $delimiter = '/'
    ) {
    }

    public function atomic(string $pattern): string
    {
        return sprintf('(?>%s)', $pattern);
    }

    public function positiveLookahead(string $pattern): string
    {
        return sprintf('(?=%s)', $pattern);
    }

    public function negativeLookahead(string $pattern): string
    {
        return sprintf('(?!%s)', $pattern);
    }

    public function patternFor(Expression $expr): string
    {
        if ($expr instanceof Literal) {
            $pattern = preg_quote($expr->getLiteral(), $this->delimiter);
            return self::escapeLiteral($pattern);
        }
        if ($expr instanceof EOF) {
            return '\z';
        }
        if ($expr instanceof AbstractRegExp || $expr instanceof GroupMatch) {
            return $this->patternForMatch($expr);
        }
        if ($expr instanceof Quantifier) {
            return $this->patternForQuantifier($expr);
        }

        throw new \LogicException(sprintf('Cannot compile %s to PCRE pattern.', Str::className($expr)));
    }

    public function hasUnmergeableFlags(AbstractRegExp $expr): bool
    {
        foreach ($expr->getFlags() as $flag) {
            if (!in_array($flag, self::MERGEABLE_FLAGS, true)) {
                return true;
            }
        }

        return false;
    }

    protected function patternForQuantifier(Quantifier $expr): string
    {
        if ($expr->isZeroOrMore()) {
            return '*';
        }
        if ($expr->isOneOrMore()) {
            return '+';
        }
        if ($expr->isOptional()) {
            return '?';
        }
        if ($expr->isExact()) {
            return sprintf('{%d}', $expr->getLowerBound());
        }

        return sprintf(
            '{%d,%s}',
            $expr->getLowerBound(),
            $expr->isUnbounded() ? '' : $expr->getUpperBound()
        );
    }

    /**
     * @param AbstractRegExp|GroupMatch $expr
     * @return string
     */
    protected function patternForMatch($expr): string
    {
        if ($flags = $expr->getFlags()) {
            return sprintf(
                '(?%s:%s)',
                implode('', $flags),
                $expr->getPattern()
            );
        }

        return $expr->getPattern();
    }

    private static function escapeLiteral(string $input): string
    {
        return strtr($input, [
            " " => '\x20',
            "\n" => '\n',
            "\r" => '\r',
            "\t" => '\t',
            "\f" => '\f',
            "\v" => '\x0b',
        ]);
    }
}
