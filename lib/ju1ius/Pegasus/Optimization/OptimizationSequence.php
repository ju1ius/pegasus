<?php

namespace ju1ius\Pegasus\Optimization;


class OptimizationSequence extends Optimization
{
    public function __construct(Optimization $first, Optimization $last)
    {
        $this->first = $first;
        $this->last = $last;
    }

    protected function _appliesTo(Expression $expr)
    {
        return $this->last->appliesTo($expr)
            || $this->first->appliesTo($expr)
        ;
    }
    protected function _apply(Expression $expr)
    {
        return $this->last->apply($this->first->apply($expr));
    }
}
