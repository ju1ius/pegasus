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

class Optimization
{
    protected $appliesToCache = [];

    public function add(Optimization $other)
    {
        return new OptimizationSequence($this, $other);
    }

    public function apply(Expression $expr)
    {
        return $this->appliesTo($expr) ? $this->_apply($expr) : $expr;
    }

    public function appliesTo(Expression $expr)
    {
        $key = spl_object_hash($expr);
        if (!isset($this->appliesToCache[$key])) {
            $this->appliesToCache[$key] = $this->_appliesTo($expr);
        }

        return $this->appliesToCache[$key];
    }
}
