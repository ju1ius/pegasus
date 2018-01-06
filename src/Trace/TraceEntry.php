<?php declare(strict_types=1);


namespace ju1ius\Pegasus\Trace;


use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\Expression;


class TraceEntry implements \IteratorAggregate
{
    /**
     * @var Expression
     */
    public $expression;

    /**
     * @var int
     */
    public $depth;

    /**
     * @var int
     */
    public $start;

    /**
     * @var int
     */
    public $end;

    /**
     * @var Node|true|null
     */
    public $result;

    /**
     * @var TraceEntry[]
     */
    public $children = [];

    public function __construct(Expression $expr, int $depth)
    {
        $this->expression = $expr;
        $this->depth = $depth;
    }

    public function getIterator()
    {
        foreach ($this->children as $child) {
            yield $child;
            yield from $child;
        }
    }
}