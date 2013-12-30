<?php

namespace ju1ius\Pegasus\Packrat;

use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Node;
use ju1ius\Pegasus\Exception\ParseError;


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

    public function apply(Expression $expr, $pos=0)
    {
        //echo __METHOD__, ' @', $this->pos, ': trying ',
            //$expr, "\n";

        $start_pos = $pos;
        $this->pos = $pos;
        $this->updateError($expr);
        if ($memo = $this->memo($expr, $start_pos)) {
            return $this->recall($memo, $expr);
        }
        $memo = $this->inject_fail($expr, $start_pos);
        //$result = $this->evaluate($expr);
        $result = $expr->match($this->source, $start_pos, $this);
        //if ($result) echo "MATCHED!\n";
        return $this->save($memo, $result);
    }

    public function evaluate(Expression $expr)
    {
        $result = $expr->match($this->source, $this->pos, $this);
        return $result;
    }

    protected function updateError(Expression $expr)
    {
        $this->error->pos = $this->pos;
        $this->error->expr = $expr;
    }
    
    protected function memo(Expression $expr, $start_pos)
    {
        return isset($this->memo[$expr->id][$start_pos])
            ? $this->memo[$expr->id][$start_pos]
            : null
        ;
    }

    protected function inject_memo(Expression $expr, $start_pos, $result, $end_pos)
    {
        return $this->memo[$expr->id][$start_pos] = new MemoEntry($result, $end_pos);
    }

    protected function inject_fail(Expression $expr, $fail_pos)
    {
        return $this->memo[$expr->id][$fail_pos] = new MemoEntry(null, $fail_pos);
    }

    protected function save($memo, $result)
    {
        //echo __METHOD__, '@', $this->pos, ': ',
            //$result ?: 'NULL', "\n";
        if ($result) {
            $this->pos = $result->end;
        }
        $memo->end = $this->pos;
        $memo->result = $result;
        return $result;
    }

    protected function recall(MemoEntry $memo, Expression $expr)
    {
        //echo __METHOD__, '@', $this->pos, ': ',
            //$expr, "\n";
        $this->pos = $memo->end;
        return $memo->result;
    }
}
