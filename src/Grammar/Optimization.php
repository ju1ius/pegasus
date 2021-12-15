<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Grammar;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Grammar;

/**
 * A transformation of an expression into an equivalent expression that can result in more efficient parsing code.
 * Optimizations are grammar visitors and are used in OptimizationPass instances.
 */
abstract class Optimization
{
    /**
     * Called once before grammar traversal.
     * Returning null leaves the grammar unchanged.
     */
    public function beforeTraverse(Grammar $grammar, OptimizationContext $context): ?Grammar
    {
        return null;
    }

    /**
     * Called once after grammar traversal.
     * Returning null leaves the grammar unchanged.
     */
    public function afterTraverse(Grammar $grammar, OptimizationContext $context): ?Grammar
    {
        return null;
    }

    /**
     * Returns whether the `preProcessRule` method should be called by the optimizer.
     */
    public function willPreProcessRule(Grammar $grammar, Expression $expr, OptimizationContext $context): bool
    {
        return false;
    }

    /**
     * Called before visiting each rule.
     * Returning null leaves the grammar unchanged.
     * Returning false removes the rule from the grammar.
     * Returning an Expression will replace the rule.
     */
    public function preProcessRule(Grammar $grammar, Expression $expr, OptimizationContext $context): Expression|false|null
    {
        return null;
    }

    /**
     * Returns whether the `postProcessRule` method should be called by the optimizer.
     */
    public function willPostProcessRule(Grammar $grammar, Expression $expr, OptimizationContext $context): bool
    {
        return false;
    }

    /**
     * Called after visiting each rule.
     * Returning null leaves the grammar unchanged.
     * Returning false removes the rule from the grammar.
     * Returning an Expression will replace the rule.
     */
    public function postProcessRule(Grammar $grammar, Expression $expr, OptimizationContext $context): Expression|false|null
    {
        return null;
    }

    /**
     * Returns whether the `preProcessExpression` method should be called by the optimizer.
     */
    public function willPreProcessExpression(Expression $expr, OptimizationContext $context): bool
    {
        return false;
    }

    public function preProcessExpression(Expression $expr, OptimizationContext $context): ?Expression
    {
        return null;
    }

    /**
     * Returns whether the `postProcessExpression` of this optimization should be called by the optimizer.
     */
    public function willPostProcessExpression(Expression $expr, OptimizationContext $context): bool
    {
        return false;
    }

    public function postProcessExpression(Expression $expr, OptimizationContext $context): ?Expression
    {
        return null;
    }
}
