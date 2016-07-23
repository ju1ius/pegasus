<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Compiler\Extension\Php\Runtime;

use ju1ius\Pegasus\CST\Node;

/**
 * LeftRecursion’s `seed` field holds the initial parse found for the associated `rule`.
 *
 * The `head` field, for a left-recursive invocation, holds information pertinent to the left recursion
 * (`head` is set to NULL for non-left-recursive invocations).
 */
final class LeftRecursion
{
    /**
     * The expression we're currently matching.
     *
     * @var string
     */
    public $rule;

    /**
     * The initial parse tree found for the associated `rule`
     *
     * @var Node|null
     */
    public $seed;

    /**
     * Holds information pertinent to the left recursion (null for non-left-recursive invocations)
     *
     * @var Head|null
     */
    public $head;

    /**
     * @param string $ruleName
     * @param Node   $seed
     * @param Head   $head
     */
    public function __construct($ruleName, Node $seed = null, Head $head = null)
    {
        $this->rule = $ruleName;
        $this->seed = $seed;
        $this->head = $head;
    }
}
