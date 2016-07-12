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
use ju1ius\Pegasus\Expression\Assert;
use ju1ius\Pegasus\Expression\EOF;
use ju1ius\Pegasus\Expression\Match;
use ju1ius\Pegasus\Expression\Not;
use ju1ius\Pegasus\Expression\Sequence;
use ju1ius\Pegasus\Optimization\OptimizationContext;

/**
 * @author ju1ius
 */
trait PredicateMatchSequenceJoinerTrait
{
    use PredicateMatchJoinerTrait;

    /**
     * @inheritDoc
     */
    protected function doAppliesTo(Expression $expr, OptimizationContext $context)
    {
        return $expr instanceof Sequence && $this->someEligiblePairs($expr);
    }

    protected function joinPatterns(array $patterns)
    {
        return implode('', $patterns);
    }

    /**
     * @param Expression $child
     *
     * @return string
     *
     * @todo FLAGS!!!
     */
    protected function prepareBarePattern(Expression $child)
    {
        if ($child instanceof Match) {
            return sprintf('(?>%s)', $child->pattern);
        }
        if ($child instanceof Assert) {
            return sprintf('(?=%s)', $child[0]->pattern);
        }
        if ($child instanceof Not) {
            return sprintf('(?!%s)', $child[0]->pattern);
        }
        if ($child instanceof EOF) {
            return '\z';
        }
    }
}
