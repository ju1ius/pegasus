<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Parser;

use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\Expression;

/**
 * LeftRecursion’s `seed` field holds the initial parse found for the associated `rule`.
 * The `head` field, for a left-recursive invocation,
 * holds information pertinent to the left recursion
 * (`head` is set to NULL for non-left-recursive invocations).
 */
final class LeftRecursion
{
    public function __construct(
        /** The expression we're currently matching. */
        public Expression $rule,
        /** The initial parse tree found for the associated `rule` */
        public Node|bool|null $seed = null,
        /**
         * Holds information pertinent to the left recursion
         * (null for non-left-recursive invocations)
         */
        public ?Head $head = null
    ) {
    }
}
