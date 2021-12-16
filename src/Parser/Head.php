<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Parser;

use ju1ius\Pegasus\Expression;

/**
 * The Head data type contains the head rule of the left recursion,
 * and the following two sets of rules:
 * - `involved`, for the rules involved in the left recursion.
 * - `eval`, which holds the subset of the involved rules
 *   that may still be evaluated during the current growth cycle.
 */
final class Head
{
    /**
     * The head rule of the left recursion.
     */
    public Expression $rule;

    /**
     * The set of rules involved in the left recursion.
     * @var Expression[]
     */
    public array $involved = [];

    /**
     * The subset of the involved rules that may still be evaluated during the current growth cycle.
     * @var Expression[]
     */
    public array $eval = [];

    public function __construct(Expression $rule)
    {
        $this->rule = $rule;
    }

    /**
     * Returns whether the given expression is involved in this left recursion.
     */
    public function involves(Expression $rule): bool
    {
        return $this->rule->id === $rule->id || isset($this->involved[$rule->id]);
    }
}
