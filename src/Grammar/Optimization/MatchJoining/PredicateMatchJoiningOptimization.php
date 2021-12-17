<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Grammar\Optimization\MatchJoining;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Composite;
use ju1ius\Pegasus\Expression\Decorator\Assert;
use ju1ius\Pegasus\Expression\Decorator\Not;
use ju1ius\Pegasus\Expression\Terminal\EOF;
use ju1ius\Pegasus\Expression\Terminal\Literal;
use ju1ius\Pegasus\Expression\Terminal\RegExp;
use ju1ius\Pegasus\Grammar\Optimization\CompositeReducerTrait;
use ju1ius\Pegasus\Grammar\Optimization\RegExpOptimization;
use ju1ius\Pegasus\Grammar\OptimizationContext;
use ju1ius\Pegasus\Utils\Iter;

abstract class PredicateMatchJoiningOptimization extends RegExpOptimization
{
    use CompositeReducerTrait;

    public function postProcessExpression(Expression $expr, OptimizationContext $context): ?Expression
    {
        $children = [];
        foreach ($expr as $child) {
            $last = end($children);
            if ($last && $this->isEligiblePair($last, $child)) {
                array_pop($children);
                $children[] = $this->reduce($last, $child);
            } else {
                $children[] = $child;
            }
        }

        return $this->finishReduction($expr, $children);
    }

    /**
     * @return RegExp
     */
    protected function reduce(Expression ...$pair): Expression
    {
        $patterns = array_map($this->preparePattern(...), $pair);
        $pattern = $this->joinPatterns($patterns);

        return new RegExp($pattern);
    }

    abstract protected function preparePattern(Expression $child): ?string;

    /**
     * @param string[] $patterns
     */
    abstract protected function joinPatterns(array $patterns): string;

    protected function isEligibleMatch(Expression $expr): bool
    {
        return $expr instanceof RegExp || $expr instanceof Literal;
    }

    protected function isEligiblePredicate(Expression $expr): bool
    {
        if ($expr instanceof EOF) {
            return true;
        }
        if ($expr instanceof Assert || $expr instanceof Not) {
            return $expr[0] instanceof RegExp || $expr[0] instanceof Literal;
        }

        return false;
    }

    protected function isEligiblePair(Expression $first, Expression $last): bool
    {
        return ($this->isEligibleMatch($first) && $this->isEligiblePredicate($last))
            || ($this->isEligiblePredicate($first) && $this->isEligibleMatch($last));
    }

    protected function someEligiblePairs(Composite $children): bool
    {
        //return Iter::some(
        //    fn($pair) => $this->isEligiblePair(...$pair),
        //    Iter::consecutive(2, $children),
        //);
        foreach (Iter::consecutive(2, $children) as [$first, $last]) {
            if ($this->isEligiblePair($first, $last)) {
                return true;
            }
        }

        return false;
    }
}
