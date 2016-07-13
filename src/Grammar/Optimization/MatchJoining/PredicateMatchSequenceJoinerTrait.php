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
use ju1ius\Pegasus\Expression\Assert;
use ju1ius\Pegasus\Expression\EOF;
use ju1ius\Pegasus\Expression\Match;
use ju1ius\Pegasus\Expression\Not;
use ju1ius\Pegasus\Expression\Sequence;
use ju1ius\Pegasus\Grammar\OptimizationContext;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
trait PredicateMatchSequenceJoinerTrait
{
    /**
     * @inheritDoc
     */
    protected function doAppliesTo(Expression $expr, OptimizationContext $context)
    {
        return $expr instanceof Sequence && $this->someEligiblePairs($expr);
    }

    /**
     * @inheritDoc
     */
    protected function joinPatterns(array $patterns)
    {
        return implode('', $patterns);
    }

    /**
     * @inheritDoc
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
