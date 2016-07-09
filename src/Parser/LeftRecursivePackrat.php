<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Parser;

use ju1ius\Pegasus\Expression;

/**
 * A packrat parser implementing Wrath, Douglass & Millstein's
 * algorithm to fully support left-recursive rules.
 *
 * @see doc/algo/packrat-lr.pdf
 */
class LeftRecursivePackrat extends Packrat
{
    /**
     * @var Head[]
     */
    protected $heads;

    /**
     * @var \SplStack<LeftRecursion>
     */
    protected $lrStack;

    public function parse($source, $pos = 0, $startStartRule = null)
    {
        $this->heads = [];
        $this->lrStack = new \SplStack();

        $result = parent::parse($source, $pos, $startStartRule);

        unset($this->heads, $this->lrStack);

        return $result;
    }

    /**
     * @param Expression $expr
     * @param int        $pos
     * @param Scope      $scope
     *
     * @return \ju1ius\Pegasus\Node|mixed|null
     */
    public function apply(Expression $expr, $pos, Scope $scope)
    {
        $this->pos = $pos;
        $this->error->position = $pos;
        $this->error->expr = $expr;

        if (!$memo = $this->recall($expr, $scope)) {
            // Store the expression in backreferences table,
            // just enough info to retrieve the result from the memo table.
            $this->refmap[$expr->name] = [$expr->id, $pos];

            // Create a new LeftRecursion and push it onto the rule invocation stack.
            $lr = new LeftRecursion($expr);
            $this->lrStack->push($lr);
            // Memoize $lr, then evaluate $expr.
            $memo = new MemoEntry($lr, $pos);
            $this->memo[$expr->id][$pos] = $memo;
            $result = $this->evaluate($expr, $scope);
            // Pop $lr off the invocation stack
            $this->lrStack->pop();
            $memo->end = $this->pos;
            if (!$lr->head) {
                $memo->result = $result;

                return $result;
            }
            $lr->seed = $result;

            return $this->lrAnswer($expr, $pos, $memo, $scope);
        }
        $this->pos = $memo->end;
        if ($memo->result instanceof LeftRecursion) {
            $this->setupLR($expr, $memo->result);

            return $memo->result->seed;
        }

        return $memo->result;
    }

    /**
     * @param Expression    $expr
     * @param LeftRecursion $lr
     */
    protected function setupLR(Expression $expr, LeftRecursion $lr)
    {
        if (!$lr->head) {
            $lr->head = new Head($expr);
        }
        foreach ($this->lrStack as $item) {
            if ($item->head === $lr->head) {
                return;
            }
            $lr->head->involved[$item->rule->id] = $item->rule;
        }
    }

    /**
     * @param Expression $expr
     * @param int        $pos
     * @param MemoEntry  $memo
     * @param Scope      $scope
     *
     * @return mixed|null
     */
    protected function lrAnswer(Expression $expr, $pos, MemoEntry $memo, Scope $scope)
    {
        $head = $memo->result->head;
        if ($head->rule->id !== $expr->id) {
            return $memo->result->seed;
        }
        $memo->result = $memo->result->seed;
        if (!$memo->result) {
            return;
        }

        return $this->growLR($expr, $pos, $memo, $head, $scope);
    }

    /**
     * @param Expression $expr
     * @param int        $pos
     * @param MemoEntry  $memo
     * @param Head       $head
     * @param Scope      $scope
     *
     * @return mixed
     */
    protected function growLR(Expression $expr, $pos, MemoEntry $memo, Head $head, Scope $scope)
    {
        $this->heads[$pos] = $head;
        while (true) {
            $this->pos = $pos;
            $head->eval = $head->involved;
            $result = $this->evaluate($expr, $scope);
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

    /**
     * @param Expression $expr
     * @param Scope      $scope
     *
     * @return MemoEntry
     */
    protected function recall(Expression $expr, Scope $scope)
    {
        $pos = $this->pos;
        // inline this to save a method call...
        // $memo = $this->memo($expr, $pos);
        /** @var MemoEntry $memo */
        $memo = isset($this->memo[$expr->id][$pos]) ? $this->memo[$expr->id][$pos] : null;
        // If not growing a seed parse, just return what is stored in the memo table.
        if (!isset($this->heads[$pos])) {
            return $memo;
        }
        $head = $this->heads[$pos];
        // Do not evaluate any rule that is not involved in this left recursion.
        if (!$memo && !$head->involves($expr)) {
            return new MemoEntry(null, $pos);
        }
        // Allow involved rules to be evaluated, but only once, during a seed-growing iteration.
        if (isset($head->eval[$expr->id])) {
            unset($head->eval[$expr->id]);
            $result = $this->evaluate($expr, $scope);
            $memo->result = $result;
            $memo->end = $this->pos;
        }

        return $memo;
    }
}
