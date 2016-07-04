<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Parser\Generated;

use ju1ius\Pegasus\Parser\MemoEntry;
use ju1ius\Pegasus\Node;

/**
 * A packrat parser implementing Wrath, Douglass & Millstein's algorithm
 * to prevent infinite loops in left-recursive rules.
 *
 * For a full implementation of left-recursion, use LeftRecursiveParser.
 *
 * @see doc/algo/packrat-lr.pdf
 */
class Packrat extends RecursiveDescent
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
     * @param int    $position
     *
     * @return Node|null
     */
    protected function apply($ruleName, $position = 0)
    {
        $this->pos = $position;
        $this->error->position = $position;
        $this->error->expr = $ruleName;

        if (isset($this->memo[$ruleName][$position])) {
            $memo = $this->memo[$ruleName][$position];
            $this->pos = $memo->end;

            return $memo->result;
        }

        // Store a result of FAIL in the memo table before it evaluates the body of a rule.
        // This has the effect of making all left-recursive applications (both direct and indirect) fail.
        $memo = new MemoEntry(null, $position);
        $this->memo[$ruleName][$position] = $memo;
        // evaluate expression
        $result = $this->evaluate($ruleName);
        // update the result in the memo table
        $memo->result = $result;
        $memo->end = $this->pos;

        return $result;
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
        return isset($this->memo[$ruleName][$position]) ? $this->memo[$ruleName][$position] : null;
    }
}
