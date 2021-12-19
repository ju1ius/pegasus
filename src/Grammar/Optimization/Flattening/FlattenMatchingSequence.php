<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Grammar\Optimization\Flattening;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Combinator\Sequence;
use ju1ius\Pegasus\Grammar\OptimizationContext;

/**
 * Nested sequence expressions can be flattened without affecting how they match.
 */
final class FlattenMatchingSequence extends FlatteningOptimization
{
    public function willPostProcessExpression(Expression $expr, OptimizationContext $context): bool
    {
        return parent::willPostProcessExpression($expr, $context) && $expr instanceof Sequence;
    }

    public function isEligibleChild(Expression $child): bool
    {
        return $child instanceof Sequence;
    }
}
