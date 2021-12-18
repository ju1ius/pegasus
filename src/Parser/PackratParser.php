<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Parser;

use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Parser\Memoization\MemoTable;
use ju1ius\Pegasus\Parser\Memoization\PackratMemoTable;

/**
 * A packrat parser implementing Wrath, Douglass & Millstein's algorithm
 * to prevent infinite loops in left-recursive rules.
 *
 * For a full implementation of left-recursion, use LeftRecursiveParser.
 *
 * @see doc/algo/packrat-lr.pdf
 */
class PackratParser extends RecursiveDescentParser
{
    /**
     * @var MemoTable[]
     */
    protected array $memo = [];

    protected function beforeParse(): void
    {
        parent::beforeParse();
        // TODO: MemoizationStrategy
        $this->memo = [
            //false => new SlidingMemoTable($this->grammar),
            false => new PackratMemoTable(),
            //true => new SlidingMemoTable($this->grammar),
            true => new PackratMemoTable(),
        ];
    }

    protected function afterParse($result): void
    {
        parent::afterParse($result);
        $this->memo = [];
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

    public function apply(Expression $expr): Node|bool
    {
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
        $result = $expr->matches($this->source, $this);
        // update the result in the memo table
        $memo->result = $result;
        $memo->end = $this->pos;

        return $result;
    }

    public function cut(int $position): void
    {
        parent::cut($position);
        // clear memo entries for previous positions
        foreach ($this->memo as $capturing => $table) {
            $table->cut($position);
        }
    }
}
