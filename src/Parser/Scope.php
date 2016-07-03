<?php

namespace ju1ius\Pegasus\Parser;

use ju1ius\Pegasus\Parser\Exception\UndefinedBinding;

/**
 * Represents the scope of bindings and captures in a parse sequence.
 *
 * @author ju1ius
 */
class Scope implements \IteratorAggregate, \ArrayAccess
{
    /**
     * @var array
     */
    private $bindings;

    /**
     * @var array
     */
    private $captures;

    /**
     * @var bool
     */
    private $capturesDecidable;

    /**
     * Scope constructor.
     *
     * @param array $bindings          Labeled bindings
     * @param array $captures          Captured parse results
     * @param bool  $capturesDecidable Whether the list of captured parse results can be known statically
     */
    public function __construct(array $bindings = [], array $captures = [], $capturesDecidable = true)
    {
        $this->bindings = $bindings;
        $this->captures = $captures;
        $this->capturesDecidable = $capturesDecidable;
    }

    /**
     * Creates a new empty scope.
     *
     * @return Scope
     */
    public static function void()
    {
        return new self();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $bindings = [];
        foreach ($this->bindings as $k => $v) {
            $bindings[] = "{$k} => {$v}";
        }

        return sprintf(
            '<Scope bindings: [%s]>',
            implode(', ', $bindings)
        );
    }

    /**
     * @return array
     */
    public function getBindings()
    {
        return $this->bindings;
    }

    /**
     * @return array
     */
    public function getCaptures()
    {
        return $this->captures;
    }

    /**
     * Returns whether the list of captured parse results can be known statically.
     *
     * @return bool
     */
    public function areCapturesDecidable()
    {
        return $this->capturesDecidable;
    }

    /**
     * @param array $bindings bindings to merge with the current bindings
     *
     * @return Scope A new scope with additional bindings
     */
    public function bind(array $bindings)
    {
        return new self(array_merge($this->bindings, $bindings), $this->captures, $this->capturesDecidable);
    }

    /**
     * @param array ...$captures Captures to merge with the current captures
     *
     * @return Scope A new scope with additional captures
     */
    public function capture(...$captures)
    {
        return new self($this->bindings, array_merge($this->captures, $captures), $this->capturesDecidable);
    }

    /**
     * Creates a new scope with this scope as the outer scope.
     *
     * The new scope inherits this scope's bindings, but not its captures.
     *
     * @return Scope
     */
    public function nest()
    {
        return new self($this->bindings, [], $this->capturesDecidable);
    }

    /**
     * Creates a new scope, merging bindings from this and another scope.
     *
     * @param Scope $other
     *
     * @return Scope
     */
    public function merge(Scope $other)
    {
        return $this->bind($other->bindings);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->bindings);
    }

    public function offsetExists($offset)
    {
        return isset($this->bindings[$offset]);
    }

    public function offsetGet($offset)
    {
        if (!isset($this->bindings[$offset])) {
            throw new UndefinedBinding($offset, $this);
        }

        return $this->bindings[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->bindings[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->bindings[$offset]);
    }
}
