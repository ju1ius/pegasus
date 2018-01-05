<?php declare(strict_types=1);
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Grammar\Optimization\MatchJoining;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Decorator\Assert;
use ju1ius\Pegasus\Expression\Composite;
use ju1ius\Pegasus\Expression\Terminal\EOF;
use ju1ius\Pegasus\Expression\Terminal\Match;
use ju1ius\Pegasus\Expression\Decorator\Not;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\Optimization\CompositeReducerTrait;
use ju1ius\Pegasus\Grammar\Optimization;
use ju1ius\Pegasus\Grammar\OptimizationContext;
use ju1ius\Pegasus\Utils\Iter;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
abstract class PredicateMatchJoiningOptimization extends Optimization
{
    use CompositeReducerTrait;

    /**
     * @inheritdoc
     */
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
     * @param Expression[] ...$pair
     *
     * @return Match
     */
    protected function reduce(Expression ...$pair): Expression
    {
        $patterns = array_map(function ($child) {
            return $this->preparePattern($child);
        }, $pair);
        $pattern = $this->joinPatterns($patterns);

        return new Match($pattern);
    }

    /**
     * @param Expression $child
     *
     * @return string
     */
    abstract protected function preparePattern(Expression $child): string;

    /**
     * @param string[] $patterns
     *
     * @return string
     */
    abstract protected function joinPatterns(array $patterns): string;

    /**
     * @param Expression $expr
     *
     * @return bool
     */
    protected function isEligibleMatch(Expression $expr): bool
    {
        return $expr instanceof Match;
    }

    /**
     * @param Expression $expr
     *
     * @return bool
     */
    protected function isEligiblePredicate(Expression $expr): bool
    {
        if ($expr instanceof EOF) {
            return true;
        }
        if ($expr instanceof Assert || $expr instanceof Not) {
            return $expr[0] instanceof Match;
        }

        return false;
    }

    /**
     * @param Expression $first
     * @param Expression $last
     *
     * @return bool
     */
    protected function isEligiblePair(Expression $first, Expression $last): bool
    {
        return ($this->isEligibleMatch($first) && $this->isEligiblePredicate($last))
            || ($this->isEligiblePredicate($first) && $this->isEligibleMatch($last));
    }

    /**
     * @param Composite|Expression[] $children
     *
     * @return bool
     */
    protected function someEligiblePairs($children): bool
    {
        foreach (Iter::consecutive(2, $children) as list($first, $last)) {
            if ($this->isEligiblePair($first, $last)) {
                return true;
            }
        }

        return false;
    }
}
