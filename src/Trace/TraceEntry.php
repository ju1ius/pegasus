<?php declare(strict_types=1);


namespace ju1ius\Pegasus\Trace;


use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Decorator\Not;
use ju1ius\Pegasus\Expression\TerminalExpression;


final class TraceEntry implements \IteratorAggregate
{
    public ?TraceEntry $parent = null;

    public int $index = 0;

    /**
     * @var TraceEntry[]
     */
    public array $children = [];

    public int $depth;

    public Expression $expression;

    public int $start;

    public int $end;

    /**
     * @var Node|bool
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
                $this->expression instanceof TerminalExpression
                || $this->expression instanceof Not
            );
    }
}
