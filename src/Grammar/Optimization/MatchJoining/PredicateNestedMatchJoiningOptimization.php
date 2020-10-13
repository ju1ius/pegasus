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
use ju1ius\Pegasus\Expression\Decorator\Ignore;
use ju1ius\Pegasus\Expression\Terminal\Literal;
use ju1ius\Pegasus\Expression\Terminal\Match;
use ju1ius\Pegasus\Utils\Iter;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
abstract class PredicateNestedMatchJoiningOptimization extends PredicateMatchJoiningOptimization
{
    /**
     * @param Expression[] ...$pair
     * @return Ignore
     */
    protected function reduce(Expression ...$pair): Expression
    {
        $expr = Iter::find(function (Expression $expr) {
            return $expr instanceof Ignore;
        }, $pair);

        /** @var Ignore $expr */
        return $expr->withChildren(parent::reduce(...$pair));
    }

    /**
     * @param Expression $child
     *
     * @return string
     */
    protected function preparePattern(Expression $child): string
    {
        if ($child instanceof Ignore) {
            $child = $child[0];
        }

        return $this->prepareBarePattern($child);
    }

    protected function isEligibleMatch(Expression $expr): bool
    {
        return $expr instanceof Ignore
            && ($expr[0] instanceof Match
                || $expr[0] instanceof Literal);
    }

    abstract protected function prepareBarePattern(Expression $child): string;
}
