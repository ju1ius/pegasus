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
    protected $children;

    /**
     * Composite constructor.
     *
     * Subclasses MUST always respect this constructor parameter order.
     *
     * @param Expression[]  $children
     * @param string $name
     */
    public function __construct(array $children = [], $name = '')
    {
        parent::__construct($name);
        $this->children = array_values($children);
    }

    /**
     * @inheritdoc
     */
    public function equals(Expression $other)
    {
        if (!parent::equals($other)) {
            return false;
        }
        /** @var Composite $other */
        if (count($this->children) !== $other->count()) {
            return false;
        }

        return $this->every(function (Expression $child, $i) use ($other) {
            return isset($other[$i]) && $child->equals($other[$i]);
        });
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
     * Return an of string represented children,
     * stopping descent when we hit a named node so the returned value
     * resembles the input rule.
     *
     */
    protected function stringMembers()
    {
        return array_map(function(Expression $child) {
            if ($child instanceof Reference) {
                return $child->asRightHandSide();
            }
            return $child->name ?: $child->asRightHandSide();
        }, $this->children);
    }

    //
    // Collection
    // --------------------------------------------------------------------------------------------------------------

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
     * @return $this
     */
    public function map(callable $f)
    {
        $children = [];
        foreach ($this->children as $i => $child) {
            $children[] = $f($child, $i);
        }

        return new static($children, $this->name);
    }

    /**
     * @param callable $predicate
     *
     * @return bool
     */
    public function every(callable $predicate)
    {
        foreach ($this->children as $i => $child) {
            if (!$predicate($child, $i)) {
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
            if ($predicate($child, $i)) {
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
     * @return Expression
     */
    public function offsetGet($offset)
    {
        if (!isset($this->children[$offset])) {

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
                get_class($value)
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

    public function getIterator()
    {
        return new \ArrayIterator($this->children);
    }
}
