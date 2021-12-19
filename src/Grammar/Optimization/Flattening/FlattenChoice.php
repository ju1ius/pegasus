<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Grammar\Optimization\Flattening;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Combinator\OneOf;
use ju1ius\Pegasus\Grammar\OptimizationContext;

final class FlattenChoice extends FlatteningOptimization
{
    public function willPostProcessExpression(Expression $expr, OptimizationContext $context): bool
    {
        return $expr instanceof OneOf && parent::willPostProcessExpression($expr, $context);
    }

    public function isEligibleChild(Expression $child): bool
    {
        return $child instanceof OneOf;
    }
}
