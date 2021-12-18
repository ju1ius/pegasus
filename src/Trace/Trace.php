<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Trace;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Parser\Exception\ParseError;
use ju1ius\Pegasus\Source\SourceInfo;
use ju1ius\Pegasus\Utils\Iter;
use SplStack;
use Traversable;


final class Trace implements \IteratorAggregate
{
    private SourceInfo $source;
    private SplStack $stack;
    /**
     * @var TraceEntry[]
     */
    private array $entries = [];
    private int $rightmostFailurePosition = 0;
    private ?Expression $rightmostFailure = null;

    public function __construct(string $text)
    {
        $this->source = new SourceInfo($text);
        $this->stack = new SplStack();
    }

    public function getSource(): SourceInfo
    {
        return $this->source;
    }

    public function recordFailure(Expression $expr, int $position): void
    {
        if ($position > $this->rightmostFailurePosition) {
            $this->rightmostFailurePosition = $position;
            $this->rightmostFailure = $expr;
        }
    }

    public function createParseError(): ParseError
    {
        $message = sprintf(
            "%s%s\n",
            $this->getExpectedTerminalsMessage(),
            $this->source->getExcerpt($this->rightmostFailurePosition)
        );

        return new ParseError($message, trace: $this);
    }

    public function push(Expression $expr): TraceEntry
    {
        $entry = new TraceEntry($expr, $this->stack->count());

        if ($this->stack->isEmpty()) {
            $entry->index = \count($this->entries);
            $this->entries[] = $entry;
        } else {
            $top = $this->stack->top();
            $entry->index = \count($top->children);
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

    public function getIterator(): Traversable
    {
        foreach ($this->entries as $entry) {
            yield $entry;
            yield from $entry;
        }
    }

    /**
     * @return Traversable<TraceEntry>
     */
    public function getErrorCandidates(): Traversable
    {
        return Iter::filter(
            fn(TraceEntry $entry) => $entry->isErrorCandidate($this->rightmostFailurePosition),
            $this,
        );
    }

    private function getExpectedTerminalsMessage(): string
    {
        $expected = [];
        $invocations = [];
        foreach ($this->getErrorCandidates() as $candidate) {
            $expr = $candidate->expression;
            $expected[] = $expr->getName() ?: (string)$expr;
            $invocation = Iter::map(fn($e) => $e->expression->getName(), $candidate->parentRules());
            $invocations[] = implode(' < ', iterator_to_array($invocation, false));
        }

        $length = \count($expected);
        if (!$length) {
            return '';
        }
        if ($length === 1) {
            return sprintf("Expected %s\n", $expected[0]);
        }

        $head = array_slice($expected, 0, -1);
        $tail = array_slice($expected, -1);

        return sprintf(
            "Expected one of: %s or %s\n%s\n",
            implode(', ', $head),
            $tail[0],
            implode("\n", $invocations),
        );
    }
}
