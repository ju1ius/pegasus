<?php

namespace ju1ius\Pegasus\Optimization;

use ju1ius\Pegasus\Expression;


class Optimization
{
    protected $applies_to_cache = [];

    public function add(Optimization $other)
    {
        return new OptimizationSequence($this, $other);
    }

    public function apply(Expression $expr)
    {
        return $this->appliesTo($expr)
            ? $this->_apply($expr)
            : $expr
        ;
    }

    public function appliesTo(Expression $expr)
    {
        $key = spl_object_hash($expr);
        if (!isset($this->applies_to_cache[$key])) {
            $this->applies_to_cache[$key] = $this->_appliesTo($expr);
        }
        return $this->applies_to_cache[$key];
    }
}
