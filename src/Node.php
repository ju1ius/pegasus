<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus;

/**
 * A parse tree node.
 *
 * Fields are public for performance, but should generally be treated as immutable once constructed.
 *
 * @TODO remove `ArrayAccess` implementation if it proves to be a bottleneck.
 */
class Node implements \Countable, \IteratorAggregate, \ArrayAccess
{
    /**
     * The name of this node.
     *
     * @var string
     */
    public $name;

    /**
     * The position in the text where the expression started matching.
     *
     * @var int
     */
    public $start;

    /**
     * The position after start where the expression first didn't match.
     *
     * It represents the offset _after_ the match so it's typically equal to
     * `$this->start + strlen($this->value)`.
     *
     * @var int
     */
    public $end;

    /**
     * Whether this node should appear in the final parse tree.
     *
     * @var bool
     */
    public $isTransient = false;

    /**
     * The value of this node.
     *
     * @var string
     */
    public $value;

    /**
     * @var Node[]
     */
    public $children;

    /**
     * @var array
     */
    public $attributes;

    /**
     * @param string $name  The name of this node.
     * @param int    $start The position in the text where that name started matching
     * @param int    $end   The position after start where the name first didn't match.
     *                      It represents the offset after the match so it's typically equal to
     *                      $start + strlen($value).
     * @param null   $value The value matched by this node (only for terminals).
     * @param array  $children
     * @param array  $attributes
     */
    public function __construct($name, $start, $end, $value = null, array $children = [], array $attributes = [])
    {
        $this->name = $name;
        $this->value = $value;
        $this->start = $start;
        $this->end = $end;
        $this->children = $children;
        $this->attributes = $attributes;
    }

    /**
     * Returns a new transient node at this given position.
     *
     * A transient node signals a match that can be skipped by some expressions.
     * This kind of node is returned by zero-width assertion expressions (`Assert`, `Not`, `EOF`, `Epsilon`)
     * and `Skip` expressions.
     *
     * @param string $name
     * @param int    $start
     * @param int    $end
     *
     * @return static
     */
    public static function transient($name, $start, $end)
    {
        $node = new static($name, $start, $end);
        $node->isTransient = true;

        return $node;
    }

    /**
     * Returns a new terminal node at the given position.
     *
     * A terminal node may have a value, but has no children.
     *
     * @param string $name
     * @param int    $start
     * @param int    $end
     * @param null   $value
     * @param array  $attributes
     *
     * @return static
     */
    public static function terminal($name, $start, $end, $value = null, array $attributes = [])
    {
        return new static($name, $start, $end, $value, [], $attributes);
    }

    /**
     * Returns a new decorator node at the given position.
     *
     * A decorator node has only one child and no value.
     *
     * @param string $name
     * @param int    $start
     * @param int    $end
     * @param Node   $child
     * @param array  $attributes
     *
     * @return static
     */
    public static function decorator($name, $start, $end, Node $child, array $attributes = [])
    {
        return new static($name, $start, $end, null, [$child], $attributes);
    }

    /**
     * Returns a new composite node at the given position.
     *
     * A composite node has no value but can have any number of children.
     *
     * @param string $name
     * @param int    $start
     * @param int    $end
     * @param array  $children
     * @param array  $attributes
     *
     * @return static
     */
    public static function composite($name, $start, $end, array $children = [], array $attributes = [])
    {
        return new static($name, $start, $end, null, $children, $attributes);
    }

    public function __toString()
    {
        return $this->value ? (string)$this->value : '';
    }

    /**
     * Returns the text this node matched
     *
     * @param string $input The original input string
     *
     * @return string
     */
    public function getText($input)
    {
        $length = $this->end - $this->start;

        return $length > 0 ? substr($input, $this->start, $length) : '';
    }

    /**
     * Generator recursively yielding this node and it's children
     *
     * @return \Generator
     */
    public function iter()
    {
        yield $this;
        foreach ($this->children as $child) {
            foreach ($child->iter() as $node) {
                yield $node;
            }
        }
    }

    /**
     * Generator recursively yielding all terminal (leaf) nodes
     */
    public function terminals()
    {
        if ($this->children) {
            foreach ($this->children as $child) {
                foreach ($child->terminals() as $terminal) {
                    yield $terminal;
                }
            }
        } else {
            yield $this;
        }
    }

    public function equals(Node $other = null)
    {
        $isEqual = $other
            && $other instanceof $this
            && $other->name === $this->name
            && $other->start === $this->start
            && $other->end === $this->end
            && count($other->children) === count($this->children)
            && count($other->attributes) === count($this->attributes);
        if (!$isEqual) {
            return false;
        }
        foreach ($this->attributes as $name => $value) {
            if (!isset($other->attributes[$name]) || $other->attributes[$name] !== $this->attributes[$name]) {
                return false;
            }
        }
        foreach ($this->children as $i => $child) {
            if (!$child->equals($other->children[$i])) {
                return false;
            }
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function offsetExists($offset)
    {
        if (is_int($offset)) {
            return isset($this->children[$offset]);
        }

        return isset($this->attributes[$offset]);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset)
    {
        if (is_int($offset)) {
            return $this->children[$offset];
        }

        return isset($this->attributes[$offset]) ? $this->attributes[$offset] : null;
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $value)
    {
        if (is_int($offset)) {
            return $this->children[$offset] = $value;
        }

        return $this->attributes[$offset] = $value;
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset)
    {
        if (is_int($offset)) {
            unset($this->children[$offset]);
        }

        unset($this->attributes[$offset]);
    }

    /**
     * @inheritDoc
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->children);
    }

    /**
     * @inheritDoc
     */
    public function count()
    {
        return count($this->children);
    }
}
