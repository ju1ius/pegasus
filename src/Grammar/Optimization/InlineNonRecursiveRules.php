<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Grammar\Optimization;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Application\Reference;
use ju1ius\Pegasus\Grammar\Optimization;
use ju1ius\Pegasus\Grammar\OptimizationContext;

/**
 * References to non-recursive rules can be inlined without affecting how they parse,
 * assuming the referenced rule does not change.
 *
 * This optimization is only applied if the referenced rule is non-recursive and explicitly marked for inlining.
 */
class InlineNonRecursiveRules extends Optimization
{
    public function willPreProcessExpression(Expression $expr, OptimizationContext $context): bool
    {
        return $expr instanceof Reference && $context->isInlineableRule($expr->getIdentifier());
    }

    public function preProcessExpression(Expression $expr, OptimizationContext $context): ?Expression
    {
        /** @var Reference $expr */
        $referenced = $context->getRule($expr->getIdentifier());
        $cloned = clone $referenced;
        // In case of reference at the top-level of a rule
        $cloned->setName($expr->getName());

        return $cloned;
    }
}
