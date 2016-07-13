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
use ju1ius\Pegasus\Expression\Match;
use ju1ius\Pegasus\Expression\OneOf;
use ju1ius\Pegasus\Grammar\OptimizationContext;

/**
 * @author ju1ius
 */
trait PredicateMatchChoiceJoinerTrait
{
    /**
     * @inheritDoc
     */
    protected function doAppliesTo(Expression $expr, OptimizationContext $context)
    {
        return $expr instanceof OneOf && $this->someEligiblePairs($expr);
    }

    /**
     * @param string[] $patterns
     *
     * @return string
     */
    protected function joinPatterns(array $patterns)
    {
        return implode('|', $patterns);
    }

    protected function prepareBarePattern(Expression $child)
    {
        if ($child instanceof Match) {
            return $child->pattern;
        }
        if ($child instanceof Expression\Assert) {
            return sprintf('(?=%s)', $child[0]->pattern);
        }
        if ($child instanceof Expression\Not) {
            return sprintf('(?!%s)', $child[0]->pattern);
        }
        if ($child instanceof Expression\EOF) {
            return '\z';
        }
    }
}
