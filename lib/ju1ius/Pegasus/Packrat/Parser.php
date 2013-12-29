<?php

namespace ju1ius\Pegasus\Packrat;

use ju1ius\Pegasus\Grammar;


class Parser
{
    protected $grammar = null;
    protected $memo = [];
    protected $source = null;
    protected $pos = 0;

    public function __construct(Grammar $grammar)
    {
        $this->grammar = $grammar;
        $this->memo = [];
    }

    public function parse($source, $rule=null)
    {
        $this->source = $source;
        $this->pos = 0;
        if (!$rule) {
            $rule = $this->grammar->default_rule;
        } else {
            $rule = $this->grammar[$rule];
        }
        //FIXME: how to do this ?
        // maybe write a generator that recursively yields subexpressions ?
        // it would need to yield depth-first, ie terminal rules,
        // then parent composite rules, etc...
        $result = $this->apply($rule);
        return $result;
    }
    

    protected function apply(Expression $expr)
    {
        $start_pos = $this->pos;
        if ($m = $this->memo($expr, $start_pos)) {
            return $this->recall($m, $expr);
        }
        $m = $this->inject_fail($expr, $start_pos);
        return $this->save($m, $this->evaluate($expr));
    }

    protected function evaluate(Expression $expr)
    {
        $result = $expr->match($this->source, $this->pos);
        return $result;
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
        $this->memo[$expr->id][$start_pos] = new MemoEntry($result, $end_pos);
    }

    protected function inject_fail(Expression $expr, $fail_pos)
    {
        $this->memo[$expr->id][$fail_pos] = new MemoEntry(false, $fail_pos);
    }

    protected function save(MemoEntry $memo, $result)
    {
        $memo->end = $this->pos;
        $memo->result = $result;
        return $result;
    }

    protected function recall(MemoEntry $memo, Expression $expr)
    {
        $this->pos = $memo->end;
        return $memo->result;
    }
    
}
