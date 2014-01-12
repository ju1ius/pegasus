<?php

namespace ju1ius\Pegasus\Parser;

use ju1ius\Pegasus\Expression;


/**
 * The Head data type contains the head rule of the left recursion,
 * and the following two sets of rules:
 *
 * • $involved, for the rules involved in the left recursion, and
 * • $eval, which holds the subset of the involved rules
 *   that may still be evaluated during the current growth cycle.
 */
class Head
{
    public $rule;
    public $involved;
    public $eval;

    public function __construct(Expression $rule)
    {
        $this->rule = $rule;
        $this->involved = [];
        $this->eval = [];
    }
    
    public function involves(Expression $rule)
    {
        //return $this->rule->id === $rule->id
        return $this->rule->equals($rule->id)
            || isset($this->involved[$rule->id]);
    }
}
