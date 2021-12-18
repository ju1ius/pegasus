<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Trace;

use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Decorator\Not;
use ju1ius\Pegasus\Expression\TerminalExpression;
use ju1ius\Pegasus\Utils\Iter;
use Traversable;

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

    public Node|bool $result;

    public function __construct(Expression $expr, int $depth)
    {
        $this->expression = $expr;
        $this->depth = $depth;
    }

    /**
     * @return Traversable<TraceEntry>
     */
    public function getIterator(): Traversable
    {
        foreach ($this->children as $child) {
            yield $child;
            yield from $child;
        }
    }

    /**
     * @return iterable<TraceEntry>
     */
    public function ancestors(): iterable
    {
        for ($entry = $this->parent; $entry; $entry = $entry->parent) {
            yield $entry;
        }
    }

    public function parentRules(): iterable
    {
        yield from Iter::filter(
            fn($entry) => !!$entry->expression->getName(),
            $this->ancestors(),
        );
    }

    /**
     * We collect all failing terminal expressions at the point the parser made the most progress.
     * The inclusion of the `Not` predicate has two reasons:
     *   1. we don't want to fix code which is intended to fail
     *   2. otherwise, we're not able to distinguish syntax errors from an intended disambiguation
     */
    public function isErrorCandidate(int $rightMostFailurePosition): bool
    {
        return $this->end === $rightMostFailurePosition
            && !$this->result
            && (
                $this->expression instanceof TerminalExpression
                || $this->expression instanceof Not
            );
    }
}
