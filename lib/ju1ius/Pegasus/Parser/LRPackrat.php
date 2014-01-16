<?php

namespace ju1ius\Pegasus\Parser;

use ju1ius\Pegasus\Expression;


/**
 * A packrat parser implementing Wrath, Douglass & Millstein's
 * algorithm to fully support left-recursive rules.
 *
 * @see doc/algo/packrat-lr.pdf
 */
class LRPackrat extends Packrat
{
    protected
        $heads,
        $lr_stack;

    public function parse($source, $pos=0, $start_rule=null)
    {
        $this->heads = [];
        $this->lr_stack = new \SplStack();

        $result = parent::parse($source, $pos, $start_rule);

        unset($this->heads, $this->lr_stack);

        return $result;
    }

    public function apply(Expression $expr, $pos)
    {
        $this->pos = $pos;
        $this->error->pos = $pos;
        $this->error->expr = $expr;

        if (!$memo = $this->recall($expr, $pos)) {
            // Store the expression in backreferences table,
            // just enough info to retrieve the result from the memo table.
            $this->refmap[$expr->name] = [$expr->id, $pos];

            // Create a new LR and push it onto the rule invocation stack.
            $lr = new LR($expr);
            $this->lr_stack->push($lr);
            // Memoize $lr, then evaluate $expr.
            $memo = new MemoEntry($lr, $pos);
            $this->memo[$expr->id][$pos] = $memo;
            $result = $this->evaluate($expr, $pos);
            // Pop $lr off the invocation stack
            $this->lr_stack->pop();
            $memo->end = $this->pos;
            if (!$lr->head) {
                $memo->result = $result;
                return $result;
            }
            $lr->seed = $result;
            return $this->lr_answer($expr, $pos, $memo);
        }
        $this->pos = $memo->end;
        if ($memo->result instanceof LR) {
            $this->setup_lr($expr, $memo->result);
            return $memo->result->seed;
        }
        return $memo->result;
    }
    
    protected function setup_lr(Expression $expr, LR $lr)
    {
        if(!$lr->head) {
            $lr->head = new Head($expr);
        }
        foreach ($this->lr_stack as $item) {
            if ($item->head === $lr->head) {
                return;
            }
            $lr->head->involved[$item->rule->id] = $item->rule;
        }
    }
    
    protected function lr_answer(Expression $expr, $pos, MemoEntry $memo)
    {
        $head = $memo->result->head;
        if ($head->rule->id !== $expr->id) {
            return $memo->result->seed;
        }
        $memo->result = $memo->result->seed;
        if (!$memo->result) {
            return;
        }
        return $this->grow_lr($expr, $pos, $memo, $head);
    }

    protected function grow_lr(Expression $expr, $pos, MemoEntry $memo, Head $head)
    {
        $this->heads[$pos] = $head;
        while (true) {
            $this->pos = $pos;
            $head->eval = $head->involved;
            $result = $this->evaluate($expr);
            if (!$result || $this->pos <= $memo->end) {
                break;
            }
            $memo->result = $result;
            $memo->end = $this->pos;  /*$result->end;*/
        }
        unset($this->heads[$pos]);
        $this->pos = $memo->end;
        return $memo->result;
    }

    protected function recall(Expression $expr, $pos)
    {
        // inline this to save a method call...
        //$memo = $this->memo($expr, $pos);
        $memo = isset($this->memo[$expr->id][$start_pos])
            ? $this->memo[$expr->id][$start_pos]
            : null
        ;
        // If not growing a seed parse,
        // just return what is stored in the memo table.
        if (!isset($this->heads[$pos])) {
            return $memo;
        }
        $h = $this->heads[$pos];
        // Do not evaluate any rule
        // that is not involved in this left recursion.
        if (!$memo && !$head->involves($expr)) {
            return new MemoEntry(null, $pos);
        }
        // Allow involved rules to be evaluated,
        // but only once, during a seed-growing iteration.
        if (isset($h->eval[$expr->id])) {
            unset($h->eval[$expr->id]);
            $result = $this->evaluate($expr, $pos);
            $memo->result = $result;
            $memo->end = $this->pos;
        }
        return $memo;
    }    
}
