<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Expression;

use ju1ius\Pegasus\Expression;

/**
 * An expression which contains several other expressions.
 *
 * @author ju1ius <ju1ius@laposte.net>
 */
abstract class Composite extends Expression implements \ArrayAccess, \Countable, \IteratorAggregate
{
    /**
     * Holds an array of this expression's sub expressions.
     *
     * @var Expression[]
     */
    protected $children = [];

    /**
     * Composite constructor.
     *
     * @param Expression[] $children
     * @param string       $name
     */
    public function __construct(array $children = [], $name = '')
    {
        parent::__construct($name);
        $this->push(...$children);
    }

    /**
     * @inheritdoc
     */
    public function isCapturing()
    {
        return $this->some(function (Expression $child) {
            return $child->isCapturing();
        });
    }

    /**
     * @inheritdoc
     */
    public function isCapturingDecidable()
    {
        return $this->every(function (Expression $child) {
            return $child->isCapturingDecidable();
        });
    }

    /**
     * Returns a cloned instance with the given children.
     *
     * @param Expression[] ...$children
     *
     * @return static
     */
    public function withChildren(Expression ...$children)
    {
        $cloned = clone $this;
        $cloned->children = [];

        return $cloned->push(...$children);
    }

    //
    // Collection
    // --------------------------------------------------------------------------------------------------------------
    /**
     * @inheritDoc
     */
    public function iterate($depthFirst = false)
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
     * @param Expression[] ...$children
     *
     * @return $this
     */
    public function push(Expression ...$children)
    {
        $i = count($this->children);
        foreach ($children as $child) {
            $this->offsetSet($i++, $child);
        }

        return $this;
    }

    /**
     * @param callable $f
     *
     * @return static
     */
    public function map(callable $f)
    {
        $cloned = clone $this;
        foreach ($cloned->children as $i => $child) {
            $cloned[$i] = $f($child, $i, $cloned);
        }

        return $cloned;
    }

    /**
     * @param callable $predicate
     *
     * @return bool
     */
    public function every(callable $predicate)
    {
        foreach ($this->children as $i => $child) {
            if (!$predicate($child, $i, $this)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param callable $predicate
     *
     * @return bool
     */
    public function some(callable $predicate)
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

    /**
     * @param int $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->children[$offset]);
    }

    /**
     * @param int $offset
     *
     * @return Expression
     */
    public function offsetGet($offset)
    {
        if (!isset($this->children[$offset])) {
            throw new \LogicException('No such offset!');
        }

        return $this->children[$offset];
    }

    /**
     * @param int        $offset
     * @param Expression $value
     *
     * @return Expression|void
     */
    public function offsetSet($offset, $value)
    {
        // handle $expr[] = $child;
        if ($offset === null) {
            $offset = count($this->children);
        }
        if (!$value instanceof Expression) {
            throw new \InvalidArgumentException(sprintf(
                'Value passed to `%s` should be instance of `%s`, `%s` given.',
                __METHOD__,
                Expression::class,
                is_object($value) ? get_class($value) : gettype($value)
            ));
        }

        return $this->children[(int)$offset] = $value;
    }

    /**
     * @param int $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->children[$offset]);
    }

    //
    // Countable
    // --------------------------------------------------------------------------------------------------------------

    /**
     * @inheritdoc
     */
    public function count()
    {
        return count($this->children);
    }

    //
    // IteratorAggregate
    // --------------------------------------------------------------------------------------------------------------

    /**
     * @inheritdoc
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->children);
    }

    /**
     * Return an array of string representations of this expression's children.
     *
     * @return string[]
     */
    protected function stringChildren()
    {
        return array_map('strval', $this->children);
    }
}
