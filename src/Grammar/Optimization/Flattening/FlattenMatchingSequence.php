<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace ju1ius\Pegasus\Grammar\Optimization\Flattening;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\NamedSequence;
use ju1ius\Pegasus\Expression\Sequence;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\OptimizationContext;

/**
 * Nested sequence expressions can be flattened without affecting how they match.
 */
final class FlattenMatchingSequence extends FlatteningOptimization
{
    public function willPostProcessExpression(Expression $expr, OptimizationContext $context)
    {
        return parent::willPostProcessExpression($expr, $context) && (
            $expr instanceof Sequence
            || $expr instanceof NamedSequence
        );
    }

    public function isEligibleChild(Expression $child)
    {
        return $child instanceof Sequence;
    }
}
