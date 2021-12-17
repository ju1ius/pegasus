<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Expression;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Exception\ChildNotFound;
use ju1ius\Pegasus\Expression\Exception\InvalidChildType;

/**
 * An expression which contains several other expressions.
 */
abstract class Composite extends Expression implements \ArrayAccess, \Countable, \IteratorAggregate
{
    /**
     * Holds an array of this expression's sub expressions.
     *
     * @var Expression[]
     */
    protected array $children = [];

    /**
     * Composite constructor.
     *
     * @param Expression[] $children
     * @param string $name
     */
    public function __construct(array $children = [], string $name = '')
    {
        parent::__construct($name);
        $this->push(...$children);
    }

    public function isCapturing(): bool
    {
        return $this->some(fn(Expression $child) => $child->isCapturing());
    }

    public function isCapturingDecidable(): bool
    {
        return $this->every(fn(Expression $child) => $child->isCapturingDecidable());
    }

    /**
     * Returns a cloned instance with the given children.
     */
    public function withChildren(Expression ...$children): static
    {
        $cloned = clone $this;
        $cloned->children = [];

        return $cloned->push(...$children);
    }

    //
    // Collection
    // --------------------------------------------------------------------------------------------------------------

    public function iterate(?bool $depthFirst = false): iterable
    {
        if (!$depthFirst) yield $this;
        foreach ($this->children as $child) {
            foreach ($child->iterate($depthFirst) as $item) {
                yield $item;
            }
        }
        if ($depthFirst) yield $this;
    }

    /**
     * @return $this
     */
    public function push(Expression ...$children): static
    {
        $i = \count($this->children);
        foreach ($children as $child) {
            $this->offsetSet($i++, $child);
        }

        return $this;
    }

    public function map(callable $f): static
    {
        $cloned = clone $this;
        foreach ($cloned->children as $i => $child) {
            $cloned[$i] = $f($child, $i, $cloned);
        }

        return $cloned;
    }

    public function every(callable $predicate): bool
    {
        foreach ($this->children as $i => $child) {
            if (!$predicate($child, $i, $this)) {
                return false;
            }
        }

        return true;
    }

    public function some(callable $predicate): bool
    {
        foreach ($this->children as $i => $child) {
            if ($predicate($child, $i, $this)) {
                return true;
            }
        }

        return false;
    }

    //
    // ArrayAccess
    // --------------------------------------------------------------------------------------------------------------

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->children[$offset]);
    }

    public function offsetGet(mixed $offset): Expression
    {
        if (!isset($this->children[$offset])) {
            throw new ChildNotFound($offset);
        }

        return $this->children[$offset];
    }

    /**
     * @param int $offset
     * @param Expression $value
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (!$value instanceof Expression) {
            throw new InvalidChildType($value);
        }
        // handle $expr[] = $child;
        if ($offset === null) {
            $offset = \count($this->children);
        }

        $this->children[(int)$offset] = $value;
    }

    /**
     * @param int $offset
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->children[$offset]);
    }

    //
    // Countable
    // --------------------------------------------------------------------------------------------------------------

    public function count(): int
    {
        return \count($this->children);
    }

    //
    // IteratorAggregate
    // --------------------------------------------------------------------------------------------------------------

    public function getIterator(): \Traversable
    {
        return yield from $this->children;
    }

    /**
     * Return an array of string representations of this expression's children.
     *
     * @return string[]
     */
    protected function stringChildren(): array
    {
        return array_map('strval', $this->children);
    }
}
