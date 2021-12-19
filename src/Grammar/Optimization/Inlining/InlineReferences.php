<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Grammar\Optimization\Inlining;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Application\Reference;
use ju1ius\Pegasus\Grammar\Optimization;
use ju1ius\Pegasus\Grammar\OptimizationContext;

/**
 * This optimization inlines references more aggressively than `InlineMarkedNonRecursiveRules`.
 * It is probably suitable only for use with the compiler, thus it resides in level 3.
 */
final class InlineReferences extends Optimization
{
    public function willPreProcessExpression(Expression $expr, OptimizationContext $context): bool
    {
        return $expr instanceof Reference
            && $context->getAnalysis()->isRegular($expr->getIdentifier());
    }

    public function preProcessExpression(Expression $expr, OptimizationContext $context): ?Expression
    {
        /** @var Reference $expr */
        $referenced = $context->getRule($expr->getIdentifier());
        return clone $referenced;
    }
}
