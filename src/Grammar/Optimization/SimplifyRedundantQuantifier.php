<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Grammar\Optimization;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Decorator\Quantifier;
use ju1ius\Pegasus\Expression\Decorator\ZeroOrMore;
use ju1ius\Pegasus\Grammar\Optimization;
use ju1ius\Pegasus\Grammar\OptimizationContext;

/**
 * A quantifier of a quantifier can be simplified to a simple quantifier.
 */
class SimplifyRedundantQuantifier extends Optimization
{
    public function willPostProcessExpression(Expression $expr, OptimizationContext $context): bool
    {
        return $context->isMatching()
            && $this->isSimpleQuantifier($expr)
            && $this->isSimpleQuantifier($expr[0]);
    }

    public function postProcessExpression(Expression $expr, OptimizationContext $context): ?Expression
    {
        /** @var Quantifier $expr */
        /** @var Quantifier $child */
        $child = $expr[0];
        if (($expr->isZeroOrMore() && $child->isZeroOrMore())
            || ($expr->isOneOrMore() && $child->isOneOrMore())
            || ($expr->isOptional() && $child->isOptional())
        ) {
            $child = clone $child;
            $child->setName($expr->getName());

            return $child;
        }

        return new ZeroOrMore($child[0], $expr->getName());
    }

    private function isSimpleQuantifier(Expression $expr): bool
    {
        return $expr instanceof Quantifier
            && ($expr->isZeroOrMore()
                || $expr->isOneOrMore()
                || $expr->isOptional()
            );

    }
}
