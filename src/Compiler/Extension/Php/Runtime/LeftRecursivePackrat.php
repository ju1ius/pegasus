<?php declare(strict_types=1);
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Compiler\Extension\Php\Runtime;

use ju1ius\Pegasus\CST\Node;

/**
 * A packrat parser implementing Wrath, Douglass & Millstein's algorithm
 * to fully support left-recursive rules.
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

    protected function beforeParse()
    {
        parent::beforeParse();
        $this->heads = [];
        $this->lrStack = new \SplStack();
    }

    protected function afterParse($result)
    {
        parent::afterParse($result);
        $this->heads = $this->lrStack = null;
    }

    /**
     * @inheritdoc
     */
    protected function apply($rule)
    {
        $memo = $this->recall($rule);

        if (!$memo) {
            $pos = $this->pos;
            // Create a new LeftRecursion and push it onto the rule invocation stack.
            $lr = new LeftRecursion($rule);
            $this->lrStack->push($lr);
            // Memoize $lr, then evaluate $name.
            $memo = new MemoEntry($lr, $pos);
            $this->memo[$this->isCapturing][$pos][$rule] = $memo;
            $result = $this->evaluate($rule);
            // Pop $lr off the invocation stack
            $this->lrStack->pop();
            $memo->end = $this->pos;
            if (!$lr->head) {
                $memo->result = $result;

                return $result;
            }
            $lr->seed = $result;

            return $this->leftRecursionAnswer($rule, $pos, $memo);
        }

        $this->pos = $memo->end;
        if ($memo->result instanceof LeftRecursion) {
            $this->setupLeftRecursion($rule, $memo->result);

            return $memo->result->seed;
        }

        return $memo->result;
    }

    /**
     * @param string        $ruleName
     * @param LeftRecursion $lr
     */
    private function setupLeftRecursion(string $ruleName, LeftRecursion $lr)
    {
        if (!$lr->head) {
            $lr->head = new Head($ruleName);
        }
        foreach ($this->lrStack as $item) {
            if ($item->head === $lr->head) {
                return;
            }
            $lr->head->involved[$item->rule] = $item->rule;
        }
    }

    /**
     * @param string    $ruleName
     * @param int       $position
     * @param MemoEntry $memo
     *
     * @return Node|LeftRecursion|null
     */
    private function leftRecursionAnswer(string $ruleName, int $position, MemoEntry $memo)
    {
        $head = $memo->result->head;
        if ($head->rule !== $ruleName) {
            return $memo->result->seed;
        }
        $memo->result = $memo->result->seed;
        if (!$memo->result) {
            return null;
        }

        return $this->growSeedParse($ruleName, $position, $memo, $head);
    }

    /**
     * @param string    $ruleName
     * @param int       $position
     * @param MemoEntry $memo
     * @param Head      $head
     *
     * @return Node|LeftRecursion|null
     */
    private function growSeedParse(string $ruleName, int $position, MemoEntry $memo, Head $head)
    {
        $this->heads[$position] = $head;
        while (true) {
            $this->pos = $position;
            $head->eval = $head->involved;
            $result = $this->evaluate($ruleName);
            if (!$result || $this->pos <= $memo->end) {
                break;
            }
            $memo->result = $result;
            $memo->end = $this->pos;  /*$result->end;*/
        }
        unset($this->heads[$position]);
        $this->pos = $memo->end;

        return $memo->result;
    }

    private function recall(string $ruleName): ?MemoEntry
    {
        $pos = $this->pos;
        // inline this to save a method call: $memo = $this->memo($name, $pos);
        /** @var MemoEntry $memo */
        $memo = $this->memo[$this->isCapturing][$pos][$ruleName] ?? null;
        $head = $this->heads[$pos] ?? null;
        // If not growing a seed parse, just return what is stored in the memo table.
        if (!$head) return $memo;
        // Do not evaluate any rule that is not involved in this left recursion.
        if (!$memo && !$head->involves($ruleName)) {
            return new MemoEntry(null, $pos);
        }
        // Allow involved rules to be evaluated, but only once, during a seed-growing iteration.
        if (isset($head->eval[$ruleName])) {
            unset($head->eval[$ruleName]);
            $result = $this->evaluate($ruleName);
            $memo->result = $result;
            $memo->end = $this->pos;
        }

        return $memo;
    }
}
