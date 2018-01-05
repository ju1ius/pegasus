<?php declare(strict_types=1);
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Grammar;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Grammar;

/**
 * A transformation of an expression into an equivalent expression that can result in more efficient parsing code.
 *
 * Optimizations are grammar visitors and are used in OptimizationPass instances.
 *
 * @author ju1ius <ju1ius@laposte.net>
 */
abstract class Optimization
{
    /**
     * Called once before grammar traversal.
     * Returning null leaves the grammar unchanged.
     *
     * @param Grammar $grammar
     * @param OptimizationContext $context
     * @return Grammar|null
     */
    public function beforeTraverse(Grammar $grammar, OptimizationContext $context): ?Grammar
    {
        return null;
    }

    /**
     * Called once after grammar traversal.
     * Returning null leaves the grammar unchanged.
     *
     * @param Grammar $grammar
     * @param OptimizationContext $context
     * @return Grammar|null
     */
    public function afterTraverse(Grammar $grammar, OptimizationContext $context): ?Grammar
    {
        return null;
    }

    /**
     * Returns whether the `preProcessRule` method should be called by the optimizer.
     *
     * @param Grammar $grammar
     * @param Expression $expr
     * @param OptimizationContext $context
     *
     * @return bool
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
     *
     * @param Grammar             $grammar
     * @param Expression          $expr
     * @param OptimizationContext $context
     *
     * @return Expression|false|null
     */
    public function preProcessRule(Grammar $grammar, Expression $expr, OptimizationContext $context)
    {
        return null;
    }

    /**
     * Returns whether the `postProcessRule` method should be called by the optimizer.
     *
     * @param Grammar $grammar
     * @param Expression $expr
     * @param OptimizationContext $context
     *
     * @return bool
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
     *
     * @param Grammar             $grammar
     * @param Expression          $expr
     * @param OptimizationContext $context
     *
     * @return Expression|false|null
     */
    public function postProcessRule(Grammar $grammar, Expression $expr, OptimizationContext $context)
    {
        return null;
    }

    /**
     * Returns whether the `preProcessExpression` method should be called by the optimizer.
     *
     * @param Expression $expr
     * @param OptimizationContext $context
     *
     * @return bool
     */
    public function willPreProcessExpression(Expression $expr, OptimizationContext $context): bool
    {
        return false;
    }

    /**
     *
     * @param Expression          $expr
     * @param OptimizationContext $context
     *
     * @return Expression|null
     */
    public function preProcessExpression(Expression $expr, OptimizationContext $context): ?Expression
    {
        return null;
    }

    /**
     * Returns whether the `postProcessExpression` of this optimization should be called by the optimizer.
     *
     * @param Expression $expr
     * @param OptimizationContext $context
     *
     * @return bool
     */
    public function willPostProcessExpression(Expression $expr, OptimizationContext $context): bool
    {
        return false;
    }

    /**
     *
     * @param Expression          $expr
     * @param OptimizationContext $context
     *
     * @return Expression|null
     */
    public function postProcessExpression(Expression $expr, OptimizationContext $context): ?Expression
    {
        return null;
    }
}
