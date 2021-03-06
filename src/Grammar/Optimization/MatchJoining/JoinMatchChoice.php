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
use ju1ius\Pegasus\Expression\Combinator\OneOf;
use ju1ius\Pegasus\Grammar\OptimizationContext;
use ju1ius\Pegasus\Utils\Iter;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
final class JoinMatchChoice extends MatchJoiningOptimization
{
    /**
     * @inheritDoc
     */
    public function willPostProcessExpression(Expression $expr, OptimizationContext $context): bool
    {
        return $expr instanceof OneOf
            && Iter::someConsecutive(function ($child) {
                return $this->isEligibleChild($child);
            }, 2, $expr);
    }

    /**
     * @inheritDoc
     */
    protected function joinPatterns(array $patterns)
    {
        return implode('|', $patterns);
    }
}
