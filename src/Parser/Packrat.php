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

use ju1ius\Pegasus\Parser\Memoization\MemoTable;
use ju1ius\Pegasus\Parser\Memoization\SlidingMemoTable;


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
     * @var MemoTable[]
     */
    protected $memo = [];

    protected function beforeParse(): void
    {
        // TODO: MemoizationStrategy
        $this->memo = [
            false => new SlidingMemoTable($this->grammar),
            true => new SlidingMemoTable($this->grammar),
        ];
        gc_disable();
    }

    protected function afterParse($result): void
    {
        gc_enable();
        $this->memo = null;
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
        $memo = $this->memo[$this->isCapturing]->get($pos, $expr);

        if ($memo) {
            $this->pos = $memo->end;
            return $memo->result;
        }

        // Store a result of FAIL in the memo table before it evaluates the body of a rule.
        // This has the effect of making all left-recursive applications (both direct and indirect) fail.
        $memo = $this->memo[$this->isCapturing]->set($pos, $expr, null);
        // evaluate expression
        $result = $this->evaluate($expr);
        // update the result in the memo table
        $memo->result = $result;
        $memo->end = $this->pos;

        return $result;
    }

    public function cut(int $position)
    {
        parent::cut($position);
        // clear memo entries for previous positions
        foreach ($this->memo as $capturing => $table) {
            $table->cut($position);
        }
    }
}
