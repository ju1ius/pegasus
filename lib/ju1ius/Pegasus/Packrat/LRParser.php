<?php

namespace ju1ius\Pegasus\Packrat;

use ju1ius\Pegasus\Expression;


class LRParser extends Parser
{
    public function __construct($source, $options)
    {
        parent::__construct($source, $options);
        $this->heads = [];
        $this->lr_stack = new \SplStack();
    }

    protected function apply(Expression $expr)
    {
        $start_pos = $this->pos;
        if ($m = $this->memo_lr($expr, $start_pos)) {
            return $this->recall($m, $expr);
        }
        $lr = new LR(false, $expr, null);
        $this->lr_stack->push($lr);
        $m = $this->inject_memo($expr, $start_pos, $lr, $start_pos);
        $result = $this->evaluate($expr);
        $this->lr_stack->pop();
        if ($lr->head) {
            $m->end = $this->pos;
            $lr->seed = $result;
            return $this->lr_answer($expr, $start_pos, $m);
        }
        return $this->save($m, $result);
    }

    protected function memo_lr(Expression $expr, $start_pos)
    {
        $m = $this->memo($expr, $start_pos);
        // If not growing a seed parse,
        // just return what is stored in the memo table.
        if (!isset($this->heads[$start_pos])) {
            return $m;
        }
        $head = $this->heads[$start_pos];
        // Do not evaluate any rule that is not involved
        // in this left recursion.
        if (!$m && !$head->involves($expr)) {
            return $this->fail($expr, $start_pos);
        }
    }

    protected function recall($m, Expression $expr)
    {
        $result = $m->result;
        if ($result instanceof LR) {
            $this->setup_lr($expr, $result);
            return $result->seed;
        }
        return parent::recall($m, $expr);
    }
    
    protected function setup_lr(Expression $expr, LR $lr)
    {
        if(!$lr->head) {
            $lr->head = new Head($expr);
        }
        foreach ($this->lr_stack as $value) {
            if ($value->head === $lr->head) return;
            $lr->head->involved[$value->rule->id] = $value->rule;
        }
    }
    
    protected function lr_answer(Expression $expr, $start_pos, MemoEntry $m)
    {
        $head = $m->result->head;
        if ($head->rule === $expr) {
            return $this->grow_lr($expr, $start_pos, $m, $head);
        }
        return $this->save($m, $m->result->seed);
    }

    protected function grow_lr(Expression $expr, $start_pos, MemoEntry $m, Head $head)
    {
        $this->heads[$start_pos] = $head;
        while (true) {
            $this->pos = $start_pos;
            $head->eval = $head->involved;
            $result = $this->evaluate($expr);
            if (!$result || $this->pos < $m->end) {
                unset($this->heads[$start_pos]);
                return $this->recall($m, $expr);
            }
            $this->save($m, $result);
        }
    }
}

