<?php

namespace ju1ius\Pegasus\Parser;

use ju1ius\Pegasus\GrammarInterface;
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
    protected $memo = [];

    /**
     * Return the parse tree matching this expression at the given position,
     * not necessarily extending all the way to the end of $text.
     *
     * @throw ParseError if there's no match there
     *
     * @return Node | null
     */
    public function parse($source, $pos=0, $rule=null)
    {
        $this->memo = [];

        $result = parent::parse($source, $pos, $rule);

        $this->memo = [];

        return $result;
    }

    /**
     * The APPLY-RULE procedure, used in every rule application,
     * ensures that no rule is ever evaluated more than once at a given position.
     *
     * When rule R is applied at position P, APPLY-RULE consults the memo table.
     * If the memo table indicates that R was previously applied at P,
     * the appropriate parse tree node is returned,
     * and the parser’s current position is updated accordingly.
     * Otherwise, APPLY-RULE evaluates the rule,
     * stores the result in the memo table,
     * and returns the corresponding parse tree node.
     */
    public function apply(Expression $expr, $pos=0)
    {
        $this->pos = $pos;
        $this->error->pos = $pos;
        $this->error->expr = $expr;

        if (isset($this->memo[$expr->id][$pos])) {
            $memo = $this->memo[$expr->id][$pos];
            $this->pos = $memo->end;
            return $memo->result;
        }

        $this->refmap[$expr->name] = [$expr->id, $pos];

        // Store a result of FAIL in the memo table
        // before it evaluates the body of a rule.
        // This has the effect of making all left-recursive applications
        // (both direct and indirect) fail.
        $memo = new MemoEntry(null, $pos);
        $this->memo[$expr->id][$pos] = $memo;
        // evaluate expression
        $result = $this->evaluate($expr);
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
     * @param int $pos
     */    
    protected function memo(Expression $expr, $start_pos)
    {
        return isset($this->memo[$expr->id][$start_pos])
            ? $this->memo[$expr->id][$start_pos]
            : null
        ;
    }
}
