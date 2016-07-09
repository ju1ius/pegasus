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

/**
 * Sequences a pair of optimizations so that applying the sequence
 * applies the second optimization to the result of applying the first optimization.
 *
 * @author ju1ius <ju1ius@laposte.net>
 */
class OptimizationSequence extends Optimization
{
    /**
     * @var Optimization
     */
    protected $first;

    /**
     * @var Optimization
     */
    protected $last;

    /**
     * @param Optimization $first
     * @param Optimization $last
     */
    public function __construct(Optimization $first, Optimization $last)
    {
        $this->first = $first;
        $this->last = $last;
    }

    /**
     * @inheritdoc
     */
    protected function doAppliesTo(Expression $expr, OptimizationContext $context)
    {
        return $this->last->appliesTo($expr, $context) || $this->first->appliesTo($expr, $context);
    }

    /**
     * @inheritdoc
     */
    protected function doApply(Expression $expr, OptimizationContext $context)
    {
        return $this->last->apply($this->first->apply($expr, $context), $context);
    }
}
