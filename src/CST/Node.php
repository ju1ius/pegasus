<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\CST;

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
     * Whether this node should appear in the final parse tree.
     *
     * @var bool
     */
    public $isTransient = false;

    /**
     * Whether this node is a terminal node.
     *
     * @var bool
     */
    public $isTerminal = false;

    /**
     * Whether this node is the result of a quantified match.
     *
     * @var bool
     */
    public $isQuantifier = false;

    /**
     * Whether this node is the result of an optional match (? quantifier).
     *
     * @var bool
     */
    public $isOptional = false;

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
     * Returns the text this node matched
     *
     * @param string $input The original input string
     *
     * @return string
     */
    public function getText($input)
    {
        $length = $this->end - $this->start;

        return $length ? substr($input, $this->start, $length) : '';
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
