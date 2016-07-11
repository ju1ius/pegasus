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
use ju1ius\Pegasus\Expression\Sequence;
use ju1ius\Pegasus\Optimization\Optimization;
use ju1ius\Pegasus\Optimization\OptimizationContext;
use ju1ius\Pegasus\Utils\Iter;

/**
 * @todo handle literals
 *
 * @author ju1ius <ju1ius@laposte.net>
 */
class JoinMatchMatchingSequence extends Optimization
{
    use MatchJoinerTrait;

    /**
     * @inheritDoc
     *
     * @todo Should we handle NamedSequences too ?
     */
    protected function doAppliesTo(Expression $expr, OptimizationContext $context)
    {
        return $context->isMatching()
            && $expr instanceof Sequence
            && Iter::someConsecutive(function ($child) {
                return $this->isEligibleChild($child);
            }, 2, $expr);
    }

    /**
     * @todo handle non mergeable flags
     *
     * @param Expression $child
     *
     * @return bool
     */
    private function isEligibleChild(Expression $child)
    {
        return $child instanceof Match;
    }

    /**
     * @param Match $match
     *
     * @return string
     */
    protected function createPattern(Match $match)
    {
        if (count($match->flags)) {
            return sprintf('(?>(?%s)%s)', implode('', $match->flags), $match->pattern);
        }

        return sprintf('(?>%s)', $match->pattern);
    }
}
