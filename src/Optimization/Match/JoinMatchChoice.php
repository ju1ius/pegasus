<?php
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Optimization\Match;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Match;
use ju1ius\Pegasus\Expression\OneOf;
use ju1ius\Pegasus\Optimization\Optimization;
use ju1ius\Pegasus\Optimization\OptimizationContext;
use ju1ius\Pegasus\Utils\Iter;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
class JoinMatchChoice extends Optimization
{
    use MatchJoinerTrait;

    /**
     * @inheritDoc
     */
    protected function doAppliesTo(Expression $expr, OptimizationContext $context)
    {
        return $expr instanceof OneOf
            && Iter::someConsecutive(function ($child) {
                return $this->isEligibleChild($child);
            }, 2, $expr);
    }

    protected function joinPatterns(array $patterns)
    {
        return implode('|', $patterns);
    }

    private function isEligibleChild(Expression $child)
    {
        return $child instanceof Match;
    }
}
