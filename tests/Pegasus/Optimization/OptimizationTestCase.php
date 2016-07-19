<?php

namespace ju1ius\Pegasus\Tests\Optimization;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Composite;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\Optimization;
use ju1ius\Pegasus\Grammar\OptimizationContext;
use ju1ius\Pegasus\Tests\PegasusTestCase;

class OptimizationTestCase extends PegasusTestCase
{
    protected function applyOptimization(Optimization $optim, $expr, OptimizationContext $ctx = null)
    {
        if ($expr instanceof Grammar) {
            $ctx = $ctx ?: OptimizationContext::of($expr);
            $expr = $expr->getStartExpression();
        } elseif (!$ctx) {
            throw new \InvalidArgumentException(sprintf(
                'Missing OptimizationContext for expression `%s`',
                $expr
            ));
        }

        return $optim->apply($expr, $ctx, true);
    }

    protected function optimizeGrammar(Grammar $grammar, Optimization $optimization)
    {
        return $grammar->map(function ($expr, $i, $grammar) use ($optimization) {
            return $optimization->apply($expr, OptimizationContext::of($grammar), true);
        });
    }
}
