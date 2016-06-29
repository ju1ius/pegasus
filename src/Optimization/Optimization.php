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

abstract class Optimization
{
    protected $appliesToCache = [];

    /**
     * @param Optimization $other
     *
     * @return OptimizationSequence
     */
    public function add(Optimization $other)
    {
        return new OptimizationSequence($this, $other);
    }

    /**
     * @param Expression $expr
     *
     * @return Expression
     */
    final public function apply(Expression $expr)
    {
        return $this->appliesTo($expr) ? $this->doApply($expr) : $expr;
    }

    /**
     * @param Expression $expr
     *
     * @return bool
     */
    final public function appliesTo(Expression $expr)
    {
        $key = spl_object_hash($expr);
        if (!isset($this->appliesToCache[$key])) {
            $this->appliesToCache[$key] = $this->doAppliesTo($expr);
        }

        return $this->appliesToCache[$key];
    }

    /**
     * @param Expression $expr
     *
     * @return Expression
     */
    abstract protected function doApply(Expression $expr);

    /**
     * @param Expression $expr
     *
     * @return bool
     */
    abstract protected function doAppliesTo(Expression $expr);
}
