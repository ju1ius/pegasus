<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable 
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


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
    /**
     * The head rule of the left recursion.
     *
     * @var ju1ius\Pegasus\Expression
     */
    public $rule;
    /**
     * The set of rules involved in the left recursion.
     *
     * @var ju1ius\Pegasus\Expression[]
     */
    public $involved;
    /**
     * The subset of the involved rules that may still
     * be evaluated during the current growth cycle.
     *
     * @var ju1ius\Pegasus\Expression[]
     */
    public $eval;

    public function __construct(Expression $rule)
    {
        $this->rule = $rule;
        $this->involved = [];
        $this->eval = [];
    }
    
    /**
     * Returns whether the given expression is involved in this left recursion.
     *
     * @param ju1ius\Pegasus\Expression $rule
     *
     * @return bool
     */
    public function involves(Expression $rule)
    {
        //return $this->rule->id === $rule->id
        return $this->rule->id === $rule->id
            || isset($this->involved[$rule->id])
        ;
    }
}
