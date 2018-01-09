<?php declare(strict_types=1);


namespace ju1ius\Pegasus\Trace;


use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Decorator\Not;
use ju1ius\Pegasus\Expression\Terminal;


class TraceEntry implements \IteratorAggregate
{
    /**
     * @var TraceEntry
     */
    public $parent;

    /**
     * @var int
     */
    public $index = 0;

    /**
     * @var TraceEntry[]
     */
    public $children = [];

    /**
     * @var int
     */
    public $depth;

    /**
     * @var Expression
     */
    public $expression;

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

    public function parents(): \Generator
    {
        $iterator = $this->parent;
        while ($iterator) {
            yield $iterator;
            $iterator = $iterator->parent;
        }
    }

    /**
     * We collect all failing terminal expressions at the point the parser made the most progress.
     * The inclusion of the `Not` predicate has two reasons:
     *   1. we don't want to fix code which is intended to fail
     *   2. otherwise we're not able to distinguish syntax errors from an intended disambiguation
     *
     * @param int $rightMostFailurePosition
     * @return bool
     */
    public function isErrorCandidate(int $rightMostFailurePosition): bool
    {
        return $this->end === $rightMostFailurePosition
            && $this->result === null
            && (
                $this->expression instanceof Terminal
                || $this->expression instanceof Not
            );
    }
}