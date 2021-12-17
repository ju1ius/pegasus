<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Grammar\Optimization;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Decorator\Quantifier;
use ju1ius\Pegasus\Expression\Terminal\CapturingRegExp;
use ju1ius\Pegasus\Expression\Terminal\Literal;
use ju1ius\Pegasus\Expression\Terminal\RegExp;
use ju1ius\Pegasus\Grammar\OptimizationContext;

/**
 * A quantified Regexp match can be reduced to a single match.
 */
class CombineQuantifiedMatch extends RegExpOptimization
{
    public function willPostProcessExpression(Expression $expr, OptimizationContext $context): bool
    {
        return $context->isMatching()
            && $expr instanceof Quantifier
            && ($expr[0] instanceof Literal
                || $expr[0] instanceof RegExp
                || $expr[0] instanceof CapturingRegExp);
    }

    public function postProcessExpression(Expression $expr, OptimizationContext $context): ?Expression
    {
        /** @var Quantifier $expr */
        $quantifier = $this->manipulator->patternFor($expr);
        $pattern = $this->manipulator->atomic($this->manipulator->patternFor($expr[0]));

        return new RegExp(sprintf('%s%s', $pattern, $quantifier));
    }
}
