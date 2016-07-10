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
 * A packrat parser implementing Wrath, Douglass & Millstein's
 * algorithm to prevent infinite loops in left-recursive rules.
 *
 * For a full implementation of left-recursion,
 * use LRParser.
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
    public function parse($source, $pos = 0, $startRule = null)
    {
        $this->memo = [];
        $result = parent::parse($source, $pos, $startRule);
        $this->memo = [];

        return $result;
    }

    /**
     * The APPLY-RULE procedure, used in every rule application,
     * ensures that no rule is ever evaluated more than once at a given position.
     *
     * When rule R is applied at position P, APPLY-RULE consults the memo table.
     *
     * If the memo table indicates that R was previously applied at P,
     * the appropriate parse tree node is returned and the parser’s current position is updated accordingly.
     *
     * Otherwise, APPLY-RULE evaluates the rule, stores the result in the memo table,
     * and returns the corresponding parse tree node.
     *
     * @param string $rule
     * @param Scope  $scope
     *
     * @return Node|null
     * @internal param int $pos
     *
     */
    public function apply($rule, Scope $scope)
    {
        $expr = $this->grammar[$rule];
        $pos = $this->pos;
        $this->error->position = $pos;
        $this->error->expr = $expr;
        $this->error->rule = $rule;

        if (isset($this->memo[$this->isCapturing][$expr->id][$pos])) {
            $memo = $this->memo[$this->isCapturing][$expr->id][$pos];
            $this->pos = $memo->end;

            return $memo->result;
        }

        // Store a result of FAIL in the memo table
        // before it evaluates the body of a rule.
        // This has the effect of making all left-recursive applications
        // (both direct and indirect) fail.
        $memo = new MemoEntry(null, $pos);
        $this->memo[$this->isCapturing][$expr->id][$pos] = $memo;
        // evaluate expression
        $result = $this->evaluate($expr, $scope);
        // update the result in the memo table
        $memo->result = $result;
        $memo->end = $this->pos;

        return $result;
    }

    /**
     * Fetches the memo entry corresponding
     * to the given expression at the given position.
     *
     * @param Expression $expr
     * @param int        $startPosition
     *
     * @return MemoEntry|null
     */
    protected function memo(Expression $expr, $startPosition)
    {
        return isset($this->memo[$this->isCapturing][$expr->id][$startPosition])
            ? $this->memo[$this->isCapturing][$expr->id][$startPosition]
            : null;
    }
}
