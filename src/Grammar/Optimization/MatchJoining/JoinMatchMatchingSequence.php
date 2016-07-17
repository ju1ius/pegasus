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
use ju1ius\Pegasus\Expression\Literal;
use ju1ius\Pegasus\Expression\Match;
use ju1ius\Pegasus\Expression\Sequence;
use ju1ius\Pegasus\Grammar\Optimization;
use ju1ius\Pegasus\Grammar\OptimizationContext;
use ju1ius\Pegasus\Utils\Iter;

/**
 * @todo handle literals
 *
 * @author ju1ius <ju1ius@laposte.net>
 */
final class JoinMatchMatchingSequence extends MatchJoiningOptimization
{
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
     * @param Expression $expr
     *
     * @return string
     */
    protected function createPattern(Expression $expr)
    {
        if ($expr instanceof Match) {
            if (count($expr->getFlags())) {
                return sprintf('(?>(?%s)%s)', implode('', $expr->getFlags()), $expr->getPattern());
            }

            return sprintf('(?>%s)', $expr->getPattern());
        }
        if ($expr instanceof Literal) {
            return sprintf('(?>%s)', preg_quote($expr->getLiteral(), '/'));
        }
    }
}
