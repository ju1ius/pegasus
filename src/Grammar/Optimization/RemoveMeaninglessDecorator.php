<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Grammar\Optimization;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Decorator\Ignore;
use ju1ius\Pegasus\Expression\Decorator\Token;
use ju1ius\Pegasus\Grammar\Optimization;
use ju1ius\Pegasus\Grammar\OptimizationContext;

/**
 * `Token` and `Ignore` decorators only have meaning in a capturing context.
 */
class RemoveMeaninglessDecorator extends Optimization
{
    public function willPostProcessExpression(Expression $expr, OptimizationContext $context): bool
    {
        return $context->isMatching() && (
            $expr instanceof Token
            || $expr instanceof Ignore
        );
    }

    public function postProcessExpression(Expression $expr, OptimizationContext $context): ?Expression
    {
        return $expr[0];
    }
}
