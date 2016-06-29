<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


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
    /**
     * @var array
     */
    protected $heads;

    /**
     * @var \SplStack
     */
    protected $lrStack;

    public function parse($text, $pos=0, $startRule=null)
    {
        $this->heads = [];
        $this->lrStack = new \SplStack();

        $result = parent::parse($text, $pos, $startRule);

        unset($this->heads, $this->lrStack);

        return $result;
    }

    public function apply($ruleName, $pos)
    {
        $this->pos = $pos;
        $this->error->position = $pos;
        $this->error->expr = $ruleName;

        if (!$memo = $this->recall($ruleName)) {
            // Store the expression in backreferences table,
            // just enough info to retrieve the result from the memo table.
            // $this->refmap[$expr->name] = [$expr->id, $pos];

            // Create a new LR and push it onto the rule invocation stack.
            $lr = new LR($ruleName);
            $this->lrStack->push($lr);
            // Memoize $lr, then evaluate $expr.
            $memo = new MemoEntry($lr, $pos);
            $this->memo[$ruleName][$pos] = $memo;
            $result = $this->evaluate($ruleName);
            // Pop $lr off the invocation stack
            $this->lrStack->pop();
            $memo->end = $this->pos;
            if (!$lr->head) {
                $memo->result = $result;
                return $result;
            }
            $lr->seed = $result;
            return $this->lrAnswer($ruleName, $pos, $memo);
        }
        $this->pos = $memo->end;
        if ($memo->result instanceof LR) {
            $this->setupLR($ruleName, $memo->result);
            return $memo->result->seed;
        }
        return $memo->result;
    }

    protected function setupLR($ruleName, LR $lr)
    {
        if(!$lr->head) {
            $lr->head = new Head($ruleName);
        }
        foreach ($this->lrStack as $item) {
            if ($item->head === $lr->head) {
                return;
            }
            $lr->head->involved[$item->rule] = $item->rule;
        }
    }

    protected function lrAnswer($ruleName, $pos, MemoEntry $memo)
    {
        $head = $memo->result->head;
        if ($head->rule !== $ruleName) {
            return $memo->result->seed;
        }
        $memo->result = $memo->result->seed;
        if (!$memo->result) {
            return;
        }
        return $this->growLR($ruleName, $pos, $memo, $head);
    }

    protected function growLR($ruleName, $pos, MemoEntry $memo, Head $head)
    {
        $this->heads[$pos] = $head;
        while (true) {
            $this->pos = $pos;
            $head->eval = $head->involved;
            $result = $this->evaluate($ruleName);
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

    protected function recall($ruleName)
    {
        $startPos = $this->pos;
        // inline this to save a method call...
        //$memo = $this->memo($expr, $pos);
        $memo = isset($this->memo[$ruleName][$startPos]) ? $this->memo[$ruleName][$startPos] : null;
        // If not growing a seed parse,
        // just return what is stored in the memo table.
        if (!isset($this->heads[$startPos])) {
            return $memo;
        }
        $head = $this->heads[$startPos];
        // Do not evaluate any rule
        // that is not involved in this left recursion.
        if (!$memo && !$head->involves($ruleName)) {
            return new MemoEntry(null, $startPos);
        }
        // Allow involved rules to be evaluated,
        // but only once, during a seed-growing iteration.
        if (isset($head->eval[$ruleName])) {
            unset($head->eval[$ruleName]);
            $result = $this->evaluate($ruleName, $startPos);
            $memo->result = $result;
            $memo->end = $this->pos;
        }
        return $memo;
    }
}
