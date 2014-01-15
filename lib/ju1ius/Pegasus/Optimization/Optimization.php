<?php

namespace ju1ius\Pegasus\Optimization;


class Optimization
{
    protected $applies_to_cache = new \SplObjectStorage();

    public function apply(Expression $expr)
    {
        return $this->appliesTo($expr)
            ? $this->_apply($expr)
            : $expr
        ;
    }

    public function appliesTo(Expression $expr)
    {
        if (!$this->applies_to_cache->contains($expr)) {
            $this->applies_to_cache->attach($expr, $this->_appliesTo($expr));
        }
        return $this->applies_to_cache[$expr];
    }
}
