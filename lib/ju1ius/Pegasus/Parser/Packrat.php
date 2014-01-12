<?php

namespace ju1ius\Pegasus\Parser;

use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Node;
use ju1ius\Pegasus\Exception\ParseError;
use ju1ius\Pegasus\Exception\IncompleteParseError;


/**
 * A packrat parser implementing Wrath, Douglass & Millstein's
 * algorithm to prevent infinite loops in left-recursive rules.
 *
 * For a full implementation of left-recursion,
 * use LRParser.
 *
 * @see doc/algo/packrat-lr.pdf
 */
class Packrat implements ParserInterface
{
    protected $grammar = null;
    protected $memo = [];
    protected $source = null;
    public $pos = 0;
    protected $error = null;

    public function __construct(Grammar $grammar)
    {
        $this->grammar = $grammar;
    }

    /**
     * Return the parse tree matching this expression at the given position,
     * not necessarily extending all the way to the end of $text.
     *
     * @throw ParseError if there's no match there
     *
     * @return Node
     */
    public function parseAll($source, $rule=null)
    {
        $result = $this->parse($source, 0, $rule);
        if ($this->pos < strlen($source)) {
            echo $result->treeview(), "\n";
            throw new IncompleteParseError(
                $source,
                $this->pos,
                $this->error->expr
            );
        }

        return $result;
    }

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
        $this->source = $source;
        $this->pos = $pos;
        $this->memo = [];
        $this->error = new ParseError($source);
        $this->refmap = [];

        if (!$rule) {
            $rule = $this->grammar->getStartRule();
        } else {
            $rule = $this->grammar[$rule];
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
            $m = $this->memo[$expr->id][$pos];
            $this->pos = $m->end;
            return $m->result;
        }

        $this->refmap[$expr->name] = [$expr->id, $pos];

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
     *
     */
    public function evaluate(Expression $expr)
    {
        $result = $expr->match($this->source, $this->pos, $this);
        if ($result) {
            $this->pos = $result->end;
            $this->error->node = $result;
        }
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

    public function getReference($name)
    {
        // search the references map for an expression with the same name.
        if (!isset($this->refmap[$name])) return '';
        list($id, $pos) = $this->refmap[$name];
        $memo = $this->memo[$id][$pos];
        return (string) $memo->result;
    }
    
}
