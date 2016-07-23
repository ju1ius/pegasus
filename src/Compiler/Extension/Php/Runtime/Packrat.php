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
 * A packrat parser implementing Wrath, Douglass & Millstein's algorithm
 * to prevent infinite loops in left-recursive rules.
 *
 * For a full implementation of left-recursion, use LeftRecursiveParser.
 *
 * @see doc/algo/packrat-lr.pdf
 */
class Packrat extends Parser
{
    /**
     * @var array
     */
    protected $memo = [];

    /**
     * @inheritdoc
     */
    public function parse($text, $position = 0, $startRule = null)
    {
        $this->memo = [];
        $result = parent::parse($text, $position, $startRule);

        // free some memory
        $this->memo = [];

        return $result;
    }

    /**
     * The APPLY-RULE procedure, used in every rule application,
     * ensures that no rule is ever evaluated more than once at a given position.
     *
     * When rule R is applied at position P, APPLY-RULE consults the memo table.
     * If the memo table indicates that R was previously applied at P,
     * the appropriate parse tree node is returned
     * and the parser’s current position is updated accordingly.
     * Otherwise, APPLY-RULE evaluates the rule, stores the result in the memo table,
     * and returns the corresponding parse tree node.
     *
     * @param string $ruleName
     *
     * @return Node|null
     */
    protected function apply($ruleName)
    {
        $pos = $this->pos;
        $capturing = (int)$this->isCapturing;
        if (isset($this->memo[$capturing][$pos][$ruleName])) {
            $memo = $this->memo[$capturing][$pos][$ruleName];
            $this->pos = $memo->end;

            return $memo->result;
        }

        // Store a result of FAIL in the memo table before it evaluates the body of a rule.
        // This has the effect of making all left-recursive applications (both direct and indirect) fail.
        $memo = new MemoEntry(null, $pos);
        $this->memo[$capturing][$pos][$ruleName] = $memo;
        // evaluate expression
        $result = $this->evaluate($ruleName);
        // update the result in the memo table
        $memo->result = $result;
        $memo->end = $this->pos;

        return $result;
    }

    /**
     * Evaluates an expression & updates current position on success.
     *
     * @param string $ruleName
     *
     * @return Node|null
     */
    protected function evaluate($ruleName)
    {
        return $this->matchers[$ruleName]();
    }

    /**
     * Fetches the memo entry corresponding to the given expression at the given position.
     *
     * @param string $ruleName
     * @param int    $position
     *
     * @return MemoEntry|null
     */
    final protected function memo($ruleName, $position)
    {
        $capturing = (int)$this->isCapturing;

        return isset($this->memo[$capturing][$ruleName][$position])
            ? $this->memo[$capturing][$ruleName][$position]
            : null;
    }
}
