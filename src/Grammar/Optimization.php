<?php
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
     * @inheritDoc
     */
    public function beforeTraverse(Grammar $grammar)
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function afterTraverse(Grammar $grammar)
    {
        return null;
    }

    /**
     * Returns whether the `preProcessRule` method should be called by the optimizer.
     *
     * @param Grammar             $grammar
     * @param Expression          $expr
     * @param OptimizationContext $context
     *
     * @return bool
     */
    public function willPreProcessRule(Grammar $grammar, Expression $expr, OptimizationContext $context)
    {
        return false;
    }

    /**
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
     * @param Grammar             $grammar
     * @param Expression          $expr
     * @param OptimizationContext $context
     *
     * @return bool
     */
    public function willPostProcessRule(Grammar $grammar, Expression $expr, OptimizationContext $context)
    {
        return false;
    }

    /**
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
     * @param Expression          $expr
     * @param OptimizationContext $context
     *
     * @return bool
     */
    public function willPreProcessExpression(Expression $expr, OptimizationContext $context)
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
    public function preProcessExpression(Expression $expr, OptimizationContext $context)
    {
        return null;
    }

    /**
     * Returns whether the `postProcessExpression` of this optimization should be called by the optimizer.
     *
     * @param Expression          $expr
     * @param OptimizationContext $context
     *
     * @return bool
     */
    public function willPostProcessExpression(Expression $expr, OptimizationContext $context)
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
    public function postProcessExpression(Expression $expr, OptimizationContext $context)
    {
        return null;
    }
}
