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
 * Abstract class for parse tree nodes.
 *
 * Consider these immutable once constructed.
 *
 * My philosophy is that parse trees should be representation-agnostic.
 * That is, they shouldn't get mixed up with what the final rendered form of a wiki page
 * (or the intermediate representation of a programming language, or whatever) is going to be:
 * you should be able to parse once and render several representations from the tree,
 * one after another.
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
     * @var int
     */
    public $end;

    /**
     * The value of this node.
     *
     * @var string
     */
    public $value;

    /**
     * @var array
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
    public function iter() {
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
    public function terminals() {
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
            && count($other->attributes) === count($this->attributes)
        ;
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

        return $this->attributes[$offset];
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
