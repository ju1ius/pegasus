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

    public function __construct(Optimization $first, Optimization $last)
    {
        $this->first = $first;
        $this->last = $last;
    }

    protected function doAppliesTo(Expression $expr)
    {
        return $this->last->appliesTo($expr) || $this->first->appliesTo($expr);
    }

    protected function doApply(Expression $expr)
    {
        return $this->last->apply($this->first->apply($expr));
    }
}
