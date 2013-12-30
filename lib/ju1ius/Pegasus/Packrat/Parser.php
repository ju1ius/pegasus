<?php

namespace ju1ius\Pegasus\Packrat;

use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Node;
use ju1ius\Pegasus\Exception\ParseError;


/**
 * A packrat parser implementing Wrath, Douglass & Millstein's
 * algorithm to prevent infinite loops in left-recursive rules.
 *
 * For a full implementation of left-recursion,
 * use LRParser.
 *
 * @see docs/packrat-lr.pdf
 */
class Parser
{
    protected $grammar = null;
    protected $memo = [];
    protected $source = null;
    protected $pos = 0;
    protected $error = null;

    public function __construct($grammar)
    {
        if ($grammar instanceof Grammar) {
            $this->default_rule = $grammar->getDefault();
        } else if ($grammar instanceof Expression) {
            $this->default_rule = $grammar;
        } else {
            throw new \InvalidArgumentException(sprintf(
                'Argument #1 of %s must be instance of Grammar or Expression, got %s',
                __METHOD__, get_class($grammar)
            ));
        }
        $this->grammar = $grammar;
    }

    public function parse($source, $pos=0, $rule=null)
    {
        $this->source = $source;
        $this->pos = $pos;
        $this->memo = [];
        $this->error = new ParseError($source);

        if (!$rule) {
            $rule = $this->default_rule;
        } else if ($this->grammar instanceof Grammar) {
            $rule = $this->grammar[$rule];
        } else {
            // throw something
        }

        //FIXME: how to do this ?
        // maybe write a generator that recursively yields subexpressions ?
        // it would need to yield depth-first, ie terminal rules,
        // then parent composite rules, etc...
        // ATM we just pass $this to the Expression::match method,
        // and let expressions call $parser->apply for their children.
        $result = $this->apply($rule, $pos);
        if (!$result) {
            throw $this->error;
        }
        return $result;
    }

    /**
     * The APPLY-RULE procedure, used in every rule application,
     * ensures that no rule is ever evaluated more than once at a given position.
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

        if ($m = $this->memo($expr, $pos)) {
            $this->pos = $m->end;
            return $m->result;
        }
        // Store a result of FAIL in the memo table
        // before it evaluates the body of a rule.
        // This has the effect of making all left-recursive applications
        // (both direct and indirect) fail.
        $m = new MemoEntry(null, $pos);
        $this->memo[$expr->id][$pos] = $m;
        // evaluate expression
        $result = $this->evaluate($expr);
        // update the result in the memo table
        $m->result = $result;
        $m->end = $this->pos;
        return $result;
    }

    /**
     * Evaluates an expression & updates current position on success.
     */
    public function evaluate(Expression $expr)
    {
        $result = $expr->match($this->source, $this->pos, $this);
        if ($result) $this->pos = $result->end;
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
