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
        if ($position > $this->rightMostFailurePosition) {
            $this->rightMostFailurePosition = $position;
            $this->rightMostFailure = $expr;
        }
    }

    public function createParseError(): ParseError
    {
        $message = sprintf(
            "%s\n%s",
            $this->getExpectedTerminalsMessage(),
            $this->source->getExcerpt($this->rightMostFailurePosition)
        );

        return new ParseError($message);
    }

    public function createIncompleteParseError(int $position): IncompleteParseError
    {
        $message = sprintf(
            "%s\n%s",
            $this->getExpectedTerminalsMessage(),
            $this->source->getExcerpt($this->rightMostFailurePosition)
        );

        return new IncompleteParseError($message);
    }

    public function push(Expression $expr): TraceEntry
    {
        $entry = new TraceEntry($expr, $this->stack->count());

        if ($this->stack->isEmpty()) {
            $entry->index = count($this->entries);
            $this->entries[] = $entry;
        } else {
            $top = $this->stack->top();
            $entry->index = count($top->children);
            $entry->parent = $top;
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

    /**
     * @return TraceEntry[]
     */
    public function getErrorCandidates(): array
    {
        $candidates = [];
        /** @var TraceEntry $entry */
        foreach ($this->getIterator() as $entry) {
            if ($entry->isErrorCandidate($this->rightMostFailurePosition)) {
                $candidates[] = $entry;
            }
        }

        return $candidates;
    }

    private function getExpectedTerminalsMessage()
    {
        $candidates = $this->getErrorCandidates();
        $expected = [];
        foreach ($candidates as $candidate) {
            $expected[] = $candidate->expression;
        }

        return sprintf(
            'Expected one of: %s',
            implode(', ', $expected)
        );
    }
}