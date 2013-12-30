<?php

namespace ju1ius\Pegasus\Packrat;

use ju1ius\Pegasus\Expression;


class LRParser extends Parser
{
    protected
        $heads,
        $lr_stack;

    public function parse($source, $pos=0)
    {
        $this->heads = [];
        $this->lr_stack = new \SplStack();

        return parent::parse($source, $pos);
    }

    public function apply(Expression $expr, $pos)
    {
        $this->error->pos = $pos;
        $this->error->expr = $expr;

        if (!$m = $this->recall($expr, $pos)) {
            // Create a new LR and push it onto the rule invocation stack.
            $lr = new LR($expr);
            $this->lr_stack->push($lr);
            // Memoize $lr, then evaluate $expr.
            $m = new MemoEntry($lr, $pos);
            $this->memo[$expr->id][$pos] = $m;
            $result = $this->evaluate($expr, $pos);
            // Pop $lr off the invocation stack
            $this->lr_stack->pop();
            $m->end = $this->pos;
            if (!$lr->head) {
                $m->result = $result;
                return $result;
            }
            $lr->seed = $result;
            return $this->lr_answer($expr, $pos, $m);
        }
        $this->pos = $m->end;
        if ($m->result instanceof LR) {
            $this->setup_lr($expr, $m->result);
            return $m->result->seed;
        }
        return $m->result;
    }
    
    protected function setup_lr(Expression $expr, LR $lr)
    {
        if(!$lr->head) {
            $lr->head = new Head($expr);
        }
        foreach ($this->lr_stack as $item) {
            if ($item->head === $lr->head) return;
            $lr->head->involved[$item->rule->id] = $item->rule;
        }
    }
    
    protected function lr_answer(Expression $expr, $pos, MemoEntry $m)
    {
        $head = $m->result->head;
        if ($head->rule->id !== $expr->id) {
            return $m->result->seed;
        }
        $m->result = $m->result->seed;
        if (!$m->result) return;
        return $this->grow_lr($expr, $pos, $m, $head);
    }

    protected function grow_lr(Expression $expr, $pos, MemoEntry $m, Head $head)
    {
        $this->heads[$pos] = $head;
        while (true) {
            $this->pos = $pos;
            $head->eval = $head->involved;
            $result = $this->evaluate($expr, $this->pos);
            if (!$result || $this->pos <= $m->end) {
                break;
            }
            $m->result = $result;
            $m->end = /*$result->end;*/$this->pos;
        }
        $this->heads[$pos] = null;
        $this->pos = $m->end;
        return $m->result;
    }

    protected function recall(Expression $expr, $pos)
    {
        $m = $this->memo($expr, $pos);
        // If not growing a seed parse,
        // just return what is stored in the memo table.
        if (!isset($this->heads[$pos])) {
            return $m;
        }
        $h = $this->heads[$pos];
        // Do not evaluate any rule
        // that is not involved in this left recursion.
        if (!$m && !$head->involves($expr)) {
            return new MemoEntry(null, $pos);
        }
        // Allow involved rules to be evaluated,
        // but only once, during a seed-growing iteration.
        if (isset($h->eval[$expr->id])) {
            unset($h->eval[$expr->id]);
            $result = $this->evaluate($expr, $pos);
            $m->result = $result;
            $m->end = $this->pos;
        }
        return $m;
    }    
}
