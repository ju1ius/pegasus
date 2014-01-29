<?php

namespace ju1ius\Pegasus\Parser\Generated;

use ju1ius\Pegasus\Node;


/**
 *
 * LR’s $seed field holds the initial parse found for the associated rule,
 * which is stored in the $rule field.
 *
 * The $head field, for a left-recursive invocation,
 * holds information pertinent to the left recursion
 * (head is set to NIL for non-left-recursive invocations).
 */
class LR
{
    public $seed;
    public $rule;
    public $head;

    /**
     *
     * @param string    $rule_name
     * @param Node      $seed
     * @param Head      $head
     **/ 
    public function __construct($rule_name, Node $seed=null, Head $head=null)
    {
        $this->rule = $rule_name;
        $this->seed = $seed;
        $this->head = $head;
    }
}
