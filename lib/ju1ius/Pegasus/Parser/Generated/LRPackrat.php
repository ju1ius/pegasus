<?php

namespace ju1ius\Pegasus\Parser\Generated;

use ju1ius\Pegasus\Parser\MemoEntry;


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

    public function parse($text, $pos=0, $start_rule=null)
    {
        $this->heads = [];
        $this->lr_stack = new \SplStack();

        $result = parent::parse($text, $pos, $start_rule);

        unset($this->heads, $this->lr_stack);

        return $result;
    }

    public function apply($rule_name, $pos)
    {
        $this->pos = $pos;
        $this->error->pos = $pos;
        $this->error->expr = $rule_name;

        if (!$memo = $this->recall($rule_name, $pos)) {
            // Store the expression in backreferences table,
            // just enough info to retrieve the result from the memo table.
            //$this->refmap[$expr->name] = [$expr->id, $pos];

            // Create a new LR and push it onto the rule invocation stack.
            $lr = new LR($rule_name);
            $this->lr_stack->push($lr);
            // Memoize $lr, then evaluate $expr.
            $memo = new MemoEntry($lr, $pos);
            $this->memo[$rule_name][$pos] = $memo;
            $result = $this->evaluate($rule_name, $pos);
            // Pop $lr off the invocation stack
            $this->lr_stack->pop();
            $memo->end = $this->pos;
            if (!$lr->head) {
                $memo->result = $result;
                return $result;
            }
            $lr->seed = $result;
            return $this->lr_answer($rule_name, $pos, $memo);
        }
        $this->pos = $memo->end;
        if ($memo->result instanceof LR) {
            $this->setup_lr($rule_name, $memo->result);
            return $memo->result->seed;
        }
        return $memo->result;
    }
    
    protected function setup_lr($rule_name, LR $lr)
    {
        if(!$lr->head) {
            $lr->head = new Head($rule_name);
        }
        foreach ($this->lr_stack as $item) {
            if ($item->head === $lr->head) {
                return;
            }
            $lr->head->involved[$item->rule] = $item->rule;
        }
    }
    
    protected function lr_answer($rule_name, $pos, MemoEntry $memo)
    {
        $head = $memo->result->head;
        if ($head->rule !== $rule_name) {
            return $memo->result->seed;
        }
        $memo->result = $memo->result->seed;
        if (!$memo->result) {
            return;
        }
        return $this->grow_lr($rule_name, $pos, $memo, $head);
    }

    protected function grow_lr($rule_name, $pos, MemoEntry $memo, Head $head)
    {
        $this->heads[$pos] = $head;
        while (true) {
            $this->pos = $pos;
            $head->eval = $head->involved;
            $result = $this->evaluate($rule_name);
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

    protected function recall($rule_name, $pos)
    {
        // inline this to save a method call...
        //$memo = $this->memo($expr, $pos);
        $memo = isset($this->memo[$rule_name][$start_pos])
            ? $this->memo[$rule_name][$start_pos]
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
        if (!$memo && !$head->involves($rule_name)) {
            return new MemoEntry(null, $pos);
        }
        // Allow involved rules to be evaluated,
        // but only once, during a seed-growing iteration.
        if (isset($h->eval[$rule_name])) {
            unset($h->eval[$rule_name]);
            $result = $this->evaluate($rule_name, $pos);
            $memo->result = $result;
            $memo->end = $this->pos;
        }
        return $memo;
    }
}
