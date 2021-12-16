<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Compiler\Extension\Php\Runtime;

/**
 * The Head data type contains the head rule of the left recursion,
 * and the following two sets of rules:
 * - `involved`: rules involved in the left recursion
 * - `eval`: subset of the involved rules that may still be evaluated during the current growth cycle.
 */
final class Head
{
    /**
     * The head rule of the left recursion.
     */
    public string $rule;

    /**
     * The set of rules involved in the left recursion.
     * @var string[]
     */
    public array $involved = [];

    /**
     * The subset of the involved rules that may still be evaluated during the current growth cycle.
     * @var string[]
     */
    public array $eval = [];

    public function __construct(string $ruleName)
    {
        $this->rule = $ruleName;
    }

    /**
     * Returns whether the given expression is involved in this left recursion.
     */
    public function involves(string $ruleName): bool
    {
        return $this->rule === $ruleName || isset($this->involved[$ruleName]);
    }
}
