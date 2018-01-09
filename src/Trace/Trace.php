<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Trace;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Parser\Exception\IncompleteParseError;
use ju1ius\Pegasus\Parser\Exception\ParseError;
use ju1ius\Pegasus\Source\SourceInfo;


final class Trace implements \IteratorAggregate
{
    /**
     * @var SourceInfo
     */
    private $source;

    /**
     * @var \SplStack
     */
    private $stack;

    /**
     * @var TraceEntry[]
     */
    private $entries;

    /**
     * @var int
     */
    private $rightMostFailurePosition = 0;

    /**
     * @var Expression
     */
    private $rightMostFailure;

    public function __construct(string $text)
    {
        $this->source = new SourceInfo($text);
        $this->stack = new \SplStack();
    }

    public function recordFailure(Expression $expr, int $position): void
    {
        if ($position >= $this->rightMostFailurePosition) {
            $this->rightMostFailurePosition = $position;
            $this->rightMostFailure = $expr;
        }
    }

    public function getRightMostFailure(): array
    {
        return [
            $this->rightMostFailurePosition,
            $this->rightMostFailure,
        ];
    }

    public function createParseError(): ParseError
    {
        $pos = $this->rightMostFailurePosition;
        $expr = $this->rightMostFailure;
        $message = sprintf(
            "In rule `%s`, expression `%s`:\n%s\n",
            $expr->getName(),
            $expr,
            $this->source->getExcerpt($pos)
        );

        return new ParseError($message);
    }

    public function createIncompleteParseError(int $position): IncompleteParseError
    {
        $pos = $this->rightMostFailurePosition;
        $expr = $this->rightMostFailure;
        $message = sprintf(
            implode("\n", [
                "Parsing succeeded without consuming all the input.",
                "In rule `%s`, expression `%s`:"
            ]),
            $expr->getName(),
            $expr,
            $this->source->getExcerpt($pos)
        );

        return new IncompleteParseError($message);
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