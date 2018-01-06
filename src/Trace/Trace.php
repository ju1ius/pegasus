<?php declare(strict_types=1);


namespace ju1ius\Pegasus\Trace;


use ju1ius\Pegasus\Expression;


final class Trace implements \IteratorAggregate
{
    /**
     * @var \SplStack
     */
    private $stack;

    /**
     * @var TraceEntry[]
     */
    private $entries;

    public function __construct()
    {
        $this->stack = new \SplStack();
    }

    public function push(Expression $expr): TraceEntry
    {
        $entry = new TraceEntry($expr, $this->stack->count());

        if ($this->stack->isEmpty()) {
            $this->entries[] = $entry;
        } else {
            $top = $this->stack->top();
            $top->children[] = $entry;
        }

        $this->stack->push($entry);

        return $entry;
    }

    public function pop(): TraceEntry
    {
        if ($this->stack->isEmpty()) {
            return end($this->entries);
        }
        return $this->stack->pop();
    }

    public function getIterator()
    {
        foreach ($this->entries as $entry) {
            yield $entry;
            yield from $entry;
        }
    }
}