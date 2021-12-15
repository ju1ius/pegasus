<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Grammar\Optimization\MatchJoining;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Decorator\Ignore;
use ju1ius\Pegasus\Expression\Terminal\Literal;
use ju1ius\Pegasus\Expression\Terminal\NonCapturingRegExp;
use ju1ius\Pegasus\Utils\Iter;

abstract class PredicateNestedMatchJoiningOptimization extends PredicateMatchJoiningOptimization
{
    /**
     * @return Ignore
     */
    protected function reduce(Expression ...$pair): Expression
    {
        $expr = Iter::find(fn(Expression $expr) => $expr instanceof Ignore, $pair);

        /** @var Ignore $expr */
        return $expr->withChildren(parent::reduce(...$pair));
    }

    protected function preparePattern(Expression $child): ?string
    {
        if ($child instanceof Ignore) {
            $child = $child[0];
        }

        return $this->prepareBarePattern($child);
    }

    protected function isEligibleMatch(Expression $expr): bool
    {
        return $expr instanceof Ignore && (
            $expr[0] instanceof NonCapturingRegExp
            || $expr[0] instanceof Literal
        );
    }

    abstract protected function prepareBarePattern(Expression $child): ?string;
}
