<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Optimization;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Assert;
use ju1ius\Pegasus\Expression\Composite;
use ju1ius\Pegasus\Expression\Not;
use ju1ius\Pegasus\Expression\Skip;
use ju1ius\Pegasus\Expression\Token;

/**
 * A transformation of an expression into an equivalent expression that can result in more efficient parsing code.
 *
 * Subclasses override `doAppliesTo` and  `doApply` to define an optimization.
 *
 * @author ju1ius <ju1ius@laposte.net>
 */
abstract class Optimization
{
    protected $appliesToCache = [];

    /**
     * @param Optimization $other
     *
     * @return OptimizationSequence
     */
    final public function add(Optimization $other)
    {
        return new OptimizationSequence($this, $other);
    }

    /**
     * Applies an optimization to an expression in the given context.
     *
     * @param Expression          $expr
     * @param OptimizationContext $context
     * @param bool                $deep Whether to apply the optimization recursively.
     *
     * @return Expression
     */
    final public function apply(Expression $expr, OptimizationContext $context, $deep = false)
    {
        if ($deep && $expr instanceof Composite) {
            foreach ($expr as $i => $child) {
                $expr[$i] = $this->apply($child, $this->getChildContext($expr, $context), true);
            }
        }

        return $this->appliesTo($expr, $context) ? $this->doApply($expr, $context) : $expr;
    }

    /**
     * Returns whether this optimizations applies to the expression in the given context.
     *
     * @param Expression          $expr
     * @param OptimizationContext $context
     *
     * @return bool
     */
    final public function appliesTo(Expression $expr, OptimizationContext $context)
    {
        $key = sprintf('%s::%s', $expr->id, spl_object_hash($context));
        if (!isset($this->appliesToCache[$key])) {
            $this->appliesToCache[$key] = $this->doAppliesTo($expr, $context);
        }

        return $this->appliesToCache[$key];
    }

    /**
     * @param Expression          $expr
     * @param OptimizationContext $context
     *
     * @return Expression
     */
    abstract protected function doApply(Expression $expr, OptimizationContext $context);

    /**
     * @param Expression          $expr
     * @param OptimizationContext $context
     *
     * @return bool
     */
    abstract protected function doAppliesTo(Expression $expr, OptimizationContext $context);


    /**
     * @param Expression          $expr
     * @param OptimizationContext $context
     *
     * @return OptimizationContext
     */
    private function getChildContext(Expression $expr, OptimizationContext $context)
    {
        switch (get_class($expr)) {
            case Assert::class:
            case Not::class:
            case Token::class:
            case Skip::class:
                return $context->matching();
            default:
                return $context;
        }
    }
}
