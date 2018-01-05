<?php declare(strict_types=1);
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
use ju1ius\Pegasus\CST\Node;

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
    public function parse(string $text, int $pos = 0, ?string $startRule = null)
    {
        $this->memo = [];
        $result = parent::parse($text, $pos, $startRule);
        // free memory
        $this->memo = null;

        return $result;
    }

    // --------------------------------------------------------------------------------------------------------------
    // The APPLY-RULE procedure, used in every rule application,
    // ensures that no rule is ever evaluated more than once at a given position.
    //
    // When rule R is applied at position P, APPLY-RULE consults the memo table.
    //
    // If the memo table indicates that R was previously applied at P,
    // the appropriate parse tree node is returned and the parser’s current position is updated accordingly.
    //
    // Otherwise, APPLY-RULE evaluates the rule, stores the result in the memo table,
    // and returns the corresponding parse tree node.
    // --------------------------------------------------------------------------------------------------------------

    /**
     * @inheritdoc
     */
    public function apply(string $rule, bool $super = false)
    {
        $expr = $super ? $this->grammar->super($rule) : $this->grammar[$rule];
        $pos = $this->pos;

        if (isset($this->memo[$this->isCapturing][$pos][$expr->id])) {
            $memo = $this->memo[$this->isCapturing][$pos][$expr->id];
            $this->pos = $memo->end;

            return $memo->result;
        }

        // Store a result of FAIL in the memo table before it evaluates the body of a rule.
        // This has the effect of making all left-recursive applications (both direct and indirect) fail.
        $memo = new MemoEntry(null, $pos);
        $this->memo[$this->isCapturing][$pos][$expr->id] = $memo;
        // evaluate expression
        $result = $this->evaluate($expr);
        // update the result in the memo table
        $memo->result = $result;
        $memo->end = $this->pos;

        return $result;
    }
}
