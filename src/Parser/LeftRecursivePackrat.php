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
use ju1ius\Pegasus\Node;

/**
 * A packrat parser implementing Wrath, Douglass & Millstein's algorithm
 * to fully support left-recursive rules.
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

    public function parse($source, $pos = 0, $startRule = null)
    {
        $this->heads = [];
        $this->lrStack = new \SplStack();

        $result = parent::parse($source, $pos, $startRule);

        // free memory
        $this->heads = $this->lrStack = null;

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function apply($rule, Scope $scope, $super = false)
    {
        $expr = $super ? $this->grammar->super($rule) : $this->grammar[$rule];

        $pos = $this->pos;
        $this->error->position = $pos;
        $this->error->rule = $rule;
        $this->error->expr = $expr;

        if (!$memo = $this->recall($expr, $scope)) {
            // Create a new LeftRecursion and push it onto the rule invocation stack.
            $lr = new LeftRecursion($expr);
            $this->lrStack->push($lr);
            // Memoize $lr, then evaluate $expr.
            $memo = new MemoEntry($lr, $pos);
            $this->memo[$this->isCapturing][$expr->id][$pos] = $memo;
            $result = $this->evaluate($expr, $scope);
            // Pop $lr off the invocation stack
            $this->lrStack->pop();
            $memo->end = $this->pos;
            if (!$lr->head) {
                $memo->result = $result;

                return $result;
            }
            $lr->seed = $result;

            return $this->leftRecursionAnswer($expr, $pos, $memo, $scope);
        }
        $this->pos = $memo->end;
        if ($memo->result instanceof LeftRecursion) {
            $this->setupLeftRecursion($expr, $memo->result);

            return $memo->result->seed;
        }

        return $memo->result;
    }

    /**
     * @param Expression    $expr
     * @param LeftRecursion $lr
     */
    protected function setupLeftRecursion(Expression $expr, LeftRecursion $lr)
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
     * @return Node|LeftRecursion|null
     */
    protected function leftRecursionAnswer(Expression $expr, $pos, MemoEntry $memo, Scope $scope)
    {
        $head = $memo->result->head;
        if ($head->rule->id !== $expr->id) {
            return $memo->result->seed;
        }
        $memo->result = $memo->result->seed;
        if (!$memo->result) {
            return null;
        }

        return $this->growSeedParse($expr, $pos, $memo, $head, $scope);
    }

    /**
     * @param Expression $expr
     * @param int        $pos
     * @param MemoEntry  $memo
     * @param Head       $head
     * @param Scope      $scope
     *
     * @return Node|LeftRecursion|null
     */
    protected function growSeedParse(Expression $expr, $pos, MemoEntry $memo, Head $head, Scope $scope)
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
            $memo->end = $this->pos;
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
        // inline this to save a method call: $memo = $this->memo($expr, $pos);
        /** @var MemoEntry $memo */
        $memo = isset($this->memo[$this->isCapturing][$expr->id][$pos])
            ? $this->memo[$this->isCapturing][$expr->id][$pos]
            : null;
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
