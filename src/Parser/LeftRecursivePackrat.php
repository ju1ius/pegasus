<?php declare(strict_types=1);
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
use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\Parser\Memoization\MemoEntry;


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

    /**
     * @var bool
     */
    protected $isGrowingSeedParse = false;

    protected function beforeParse(): void
    {
        parent::beforeParse();
        $this->heads = [];
        $this->lrStack = new \SplStack();
    }

    protected function afterParse($result): void
    {
        parent::afterParse($result);
        $this->heads = $this->lrStack = null;
    }

    /**
     * @inheritdoc
     */
    public function apply(Expression $expr)
    {
        $pos = $this->pos;

        if (!$memo = $this->recall($expr)) {
            // Create a new LeftRecursion and push it onto the rule invocation stack.
            $lr = new LeftRecursion($expr);
            $this->lrStack->push($lr);
            // Memoize $lr
            $memo = $this->memo[$this->isCapturing]->set($pos, $expr, $lr);
            // evaluate expression
            $result = $this->evaluate($expr);
            // Pop $lr off the invocation stack
            $this->lrStack->pop();
            $memo->end = $this->pos;
            if (!$lr->head) {
                $memo->result = $result;

                return $result;
            }
            $lr->seed = $result;

            return $this->leftRecursionAnswer($expr, $pos, $memo);
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
     *
     * @return Node|LeftRecursion|null
     */
    protected function leftRecursionAnswer(Expression $expr, int $pos, MemoEntry $memo)
    {
        $head = $memo->result->head;
        if ($head->rule->id !== $expr->id) {
            return $memo->result->seed;
        }
        $memo->result = $memo->result->seed;
        if (!$memo->result) {
            return null;
        }

        return $this->growSeedParse($expr, $pos, $memo, $head);
    }

    /**
     * @param Expression $expr
     * @param int        $pos
     * @param MemoEntry  $memo
     * @param Head       $head
     *
     * @return Node|LeftRecursion|null
     */
    protected function growSeedParse(Expression $expr, int $pos, MemoEntry $memo, Head $head)
    {
        $this->isGrowingSeedParse = true;
        $this->heads[$pos] = $head;
        while (true) {
            $this->pos = $pos;
            $head->eval = $head->involved;
            $result = $this->evaluate($expr);
            if (!$result || $this->pos <= $memo->end) {
                break;
            }
            $memo->result = $result;
            $memo->end = $this->pos;
        }
        unset($this->heads[$pos]);
        $this->pos = $memo->end;
        $this->isGrowingSeedParse = false;

        return $memo->result;
    }

    protected function recall(Expression $expr): ?MemoEntry
    {
        $pos = $this->pos;
        $memo = $this->memo[$this->isCapturing]->get($pos, $expr);
        $head = $this->heads[$pos] ?? null;
        // If not growing a seed parse, just return what is stored in the memo table.
        if (!$head) return $memo;
        // Do not evaluate any rule that is not involved in this left recursion.
        if (!$memo && !$head->involves($expr)) {
            return new MemoEntry($pos, null);
        }
        // Allow involved rules to be evaluated, but only once, during a seed-growing iteration.
        if (isset($head->eval[$expr->id])) {
            unset($head->eval[$expr->id]);
            $result = $this->evaluate($expr);
            /** @var MemoEntry $memo */
            $memo->result = $result;
            $memo->end = $this->pos;
        }

        return $memo;
    }

    public function cut(int $position): void
    {
        $this->cutStack->pop();
        $this->cutStack->push(true);
        // we're growing a seed parse, don't clear anything !
        if ($this->isGrowingSeedParse) return;
        // clear memo entries for previous positions
        foreach ($this->memo as $capturing => $table) {
            $table->cut($position);
        }
    }
}
