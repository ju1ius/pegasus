<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Grammar\Optimization;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Decorator\Token;
use ju1ius\Pegasus\Expression\TerminalExpression;
use ju1ius\Pegasus\Grammar\Optimization;
use ju1ius\Pegasus\Grammar\OptimizationContext;

/**
 * A token wrapping a terminal expression is redundant.
 */
class SimplifyTerminalToken extends Optimization
{
    public function postProcessExpression(Expression $expr, OptimizationContext $context): ?Expression
    {
        return $expr[0];
    }

    public function willPostProcessExpression(Expression $expr, OptimizationContext $context): bool
    {
        return $expr instanceof Token && (
            $expr[0] instanceof TerminalExpression
            || $expr[0] instanceof Token
        );
    }
}
