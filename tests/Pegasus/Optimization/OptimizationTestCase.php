<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests\Optimization;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\Optimization;
use ju1ius\Pegasus\Grammar\OptimizationContext;
use ju1ius\Pegasus\Grammar\OptimizationPass;
use ju1ius\Pegasus\GrammarFactory;
use ju1ius\Pegasus\Tests\PegasusTestCase;

class OptimizationTestCase extends PegasusTestCase
{
    protected function applyOptimization($optim, $grammar, OptimizationContext $ctx = null)
    {
        if ($grammar instanceof Expression) {
            $rule = $grammar->getName() ?: 'start';
            $grammar = GrammarFactory::fromArray([$rule => $grammar]);
        }
        $traverser = new OptimizationPass(true);
        if (is_array($optim)) {
            $traverser->add(...$optim);
        } else {
            $traverser->add($optim);
        }
        $grammar = $traverser->process($grammar, $ctx);

        return $grammar->getStartExpression();
    }

    protected function optimizeGrammar(Grammar $grammar, Optimization $optimization)
    {
        $traverser = new OptimizationPass(true);
        $traverser->add($optimization);

        return $traverser->process($grammar);
    }
}
