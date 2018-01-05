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
use ju1ius\Pegasus\Grammar;

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

    public function parse(string $text, int $pos = 0, ?string $startRule = null)
    {
        $this->heads = [];
        $this->lrStack = new \SplStack();

        $result = parent::parse($text, $pos, $startRule);

        // free memory
        $this->heads = $this->lrStack = null;

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function apply(string $rule, bool $super = false)
    {
        $expr = $super ? $this->grammar->super($rule) : $this->grammar[$rule];
        $pos = $this->pos;

        if (!$memo = $this->recall($expr)) {
            // Create a new LeftRecursion and push it onto the rule invocation stack.
            $lr = new LeftRecursion($expr);
            $this->lrStack->push($lr);
            // Memoize $lr, then evaluate $expr.
            $memo = new MemoEntry($lr, $pos);
            $this->memo[$this->isCapturing][$pos][$expr->id] = $memo;
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

        return $memo->result;
    }

    protected function recall(Expression $expr): ?MemoEntry
    {
        $pos = $this->pos;
        $memo = isset($this->memo[$this->isCapturing][$pos][$expr->id])
            ? $this->memo[$this->isCapturing][$pos][$expr->id]
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
            $result = $this->evaluate($expr);
            /** @var MemoEntry $memo */
            $memo->result = $result;
            $memo->end = $this->pos;
        }

        return $memo;
    }
}
