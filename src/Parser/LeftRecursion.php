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
use ju1ius\Pegasus\Node;

/**
 * LeftRecursion’s `seed` field holds the initial parse found for the associated `rule`.
 *
 * The `head` field, for a left-recursive invocation, holds information pertinent to the left recursion
 * (`head` is set to NULL for non-left-recursive invocations).
 */
class LeftRecursion
{
    /**
     * The expression we're currently matching.
     *
     * @var Expression
     */
    public $rule;

    /**
     * The initial parse tree found for the associated `rule`
     *
     * @var Node
     */
    public $seed;

    /**
     * Holds information pertinent to the left recursion (null for non-left-recursive invocations)
     *
     * @var Head|null
     */
    public $head;

    /**
     * @param Expression $rule
     * @param Node       $seed
     * @param Head       $head
     */
    public function __construct(Expression $rule, Node $seed = null, Head $head = null)
    {
        $this->rule = $rule;
        $this->seed = $seed;
        $this->head = $head;
    }
}
