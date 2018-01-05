<?php declare(strict_types=1);
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Compiler\Extension\Php\Runtime;

/**
 * The Head data type contains the head rule of the left recursion,
 * and the following two sets of rules:
 *
 * - `involved`: rules involved in the left recursion
 * - `eval`: subset of the involved rules that may still be evaluated during the current growth cycle.
 */
final class Head
{
    /**
     * The head rule of the left recursion.
     *
     * @var string
     */
    public $rule;

    /**
     * The set of rules involved in the left recursion.
     *
     * @var string[]
     */
    public $involved;

    /**
     * The subset of the involved rules that may still be evaluated during the current growth cycle.
     *
     * @var string[]
     */
    public $eval;

    /**
     * Head constructor.
     *
     * @param string $ruleName
     */
    public function __construct(string $ruleName)
    {
        $this->rule = $ruleName;
        $this->involved = [];
        $this->eval = [];
    }

    /**
     * Returns whether the given expression is involved in this left recursion.
     *
     * @param string $ruleName
     *
     * @return bool
     */
    public function involves(string $ruleName): bool
    {
        return $this->rule === $ruleName || isset($this->involved[$ruleName]);
    }
}
