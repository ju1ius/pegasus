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
use ju1ius\Pegasus\Grammar\OptimizationContext;

/**
 * Nested sequence expressions can be flattened without affecting how they parse
 * if the nested sequence expressions are not multi-capturing.
 */
final class FlattenCapturingSequence extends FlatteningOptimization
{
    public function doAppliesTo(Expression $expr, OptimizationContext $context)
    {
        return parent::doAppliesTo($expr, $context) && (
            $expr instanceof Sequence
            || $expr instanceof NamedSequence
        );
    }

    public function isEligibleChild(Expression $child)
    {
        return $child instanceof Sequence && $child->getCaptureCount() <= 1;
    }
}
