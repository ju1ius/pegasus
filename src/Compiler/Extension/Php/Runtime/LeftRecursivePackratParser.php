<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Compiler\Extension\Php\Runtime;

use Closure;
use ju1ius\Pegasus\CST\Node;
use SplStack;

/**
 * A packrat parser implementing Wrath, Douglass & Millstein's algorithm
 * to fully support left-recursive rules.
 */
class LeftRecursivePackratParser extends PackratParser
{
    /**
     * @var Head[]
     */
    protected array $heads;

    /**
     * @var SplStack<LeftRecursion>
     */
    protected SplStack $lrStack;

    protected bool $isGrowingSeedParse = false;

    /**
     * @var callable[]
     */
    private array $matchers = [];

    protected function beforeParse(): void
    {
        parent::beforeParse();
        if (!$this->matchers) {
            $this->matchers = $this->buildMatchers();
        }
        $this->heads = [];
        $this->lrStack = new SplStack();
    }

    protected function afterParse($result): void
    {
        parent::afterParse($result);
        $this->heads = [];
        $this->lrStack = new SplStack();
    }

    protected function cut(int $position): void
    {
        $this->cutStack->pop();
        $this->cutStack->push(true);
        // we're growing a seed parse, don't clear anything !
        if ($this->isGrowingSeedParse) return;
        // clear memo entries for previous positions
        foreach ($this->memo as $capturing => $table) {
            foreach ($table as $pos => $rules) {
                if ($pos < $position) {
                    unset($this->memo[$capturing][$pos]);
                }
            }
        }
    }

    protected function apply(string $rule, array $bindings = []): Node|bool
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
            $result = $this->matchers[$rule]();
            // Pop $lr off the invocation stack
            $this->lrStack->pop();
            $memo->end = $this->pos;
            if (!$lr->head) {
                $memo->result = $result;

                return $result ?? false;
            }
            $lr->seed = $result;

            return $this->leftRecursionAnswer($rule, $pos, $memo) ?? false;
        }

        $this->pos = $memo->end;
        if ($memo->result instanceof LeftRecursion) {
            $this->setupLeftRecursion($rule, $memo->result);

            return $memo->result->seed ?? false;
        }

        return $memo->result ?? false;
    }

    private function setupLeftRecursion(string $ruleName, LeftRecursion $lr): void
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

    private function leftRecursionAnswer(string $ruleName, int $position, MemoEntry $memo): Node|LeftRecursion|bool
    {
        $head = $memo->result->head;
        if ($head->rule !== $ruleName) {
            return $memo->result->seed;
        }
        $memo->result = $memo->result->seed;
        if (!$memo->result) {
            return false;
        }

        return $this->growSeedParse($ruleName, $position, $memo, $head);
    }

    private function growSeedParse(string $ruleName, int $position, MemoEntry $memo, Head $head): Node|LeftRecursion|bool
    {
        $this->isGrowingSeedParse = true;
        $this->heads[$position] = $head;
        while (true) {
            $this->pos = $position;
            $head->eval = $head->involved;
            $result = $this->matchers[$ruleName]();
            if (!$result || $this->pos <= $memo->end) {
                break;
            }
            $memo->result = $result;
            $memo->end = $this->pos;
        }
        unset($this->heads[$position]);
        $this->pos = $memo->end;
        $this->isGrowingSeedParse = false;

        return $memo->result;
    }

    private function recall(string $ruleName): ?MemoEntry
    {
        $pos = $this->pos;
        /** @var MemoEntry $memo */
        $memo = $this->memo[$this->isCapturing][$pos][$ruleName] ?? null;
        $head = $this->heads[$pos] ?? null;
        // If not growing a seed parse, just return what is stored in the memo table.
        if (!$head) return $memo;
        // Do not evaluate any rule that is not involved in this left recursion.
        if (!$memo && !$head->involves($ruleName)) {
            return new MemoEntry(false, $pos);
        }
        // Allow involved rules to be evaluated, but only once, during a seed-growing iteration.
        if (isset($head->eval[$ruleName])) {
            unset($head->eval[$ruleName]);
            $result = $this->matchers[$ruleName]();
            $memo->result = $result;
            $memo->end = $this->pos;
        }

        return $memo;
    }

    /**
     * @return Closure[]
     */
    private function buildMatchers(): array
    {
        $matchers = [];
        $class = new \ReflectionClass($this);
        foreach ($class->getMethods() as $method) {
            if (str_starts_with($method->name, 'match_')) {
                $ruleName = substr($method->name, 6);
                $matchers[$ruleName] = $method->getClosure($this);
            }
        }

        return $matchers;
    }
}
