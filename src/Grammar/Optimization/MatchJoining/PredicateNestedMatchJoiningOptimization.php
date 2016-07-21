<?php
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
use ju1ius\Pegasus\Expression\Terminal\Match;
use ju1ius\Pegasus\Expression\Decorator\Skip;
use ju1ius\Pegasus\Utils\Iter;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
abstract class PredicateNestedMatchJoiningOptimization extends PredicateMatchJoiningOptimization
{
    protected function reduce(Expression ...$pair)
    {
        $expr = Iter::find(function (Expression $expr) {
            return $expr instanceof Skip;
        }, $pair);

        /** @var Skip $expr */
        return $expr->withChildren(parent::reduce(...$pair));
    }

    /**
     * @param Expression $child
     *
     * @return string
     */
    protected function preparePattern(Expression $child)
    {
        if ($child instanceof Skip) {
            $child = $child[0];
        }

        return $this->prepareBarePattern($child);
    }

    protected function isEligibleMatch(Expression $expr)
    {
        return $expr instanceof Skip && $expr[0] instanceof Match;
    }

    abstract protected function prepareBarePattern(Expression $child);
}
