<?php

namespace ju1ius\Pegasus\Expression;

use ju1ius\Pegasus\Expression;


/**
 * An abstract expression which contains several other expressions. 
 * 
 */
abstract class Composite extends Expression
{
    /**
     * Holds an array of this expression's sub expressions.
     * MUST be a zero-indexed array of Expression objects.
     *
     * @var array
     */
    public $children;

    public function __construct(array $children=[], $name='')
    {
        parent::__construct($name);
        $this->children = array_values($children);
    }

    public function equals(Expression $other)
    {
        if (!parent::equals($other)) {
            return false;
        }
        foreach ($this->children as $i => $child) {
            if (!isset($other->children[$i])) {
                return false;
            }
            if (!$child->equals($other->children[$i])) {
                return false;
            }
        }
        return true;
    }

    public function isCapturing()
    {
        foreach ($this->children as $child) {
            if ($child->isCapturing()) {
                return true;
            }   
        }
        return false;
    }

    public function isCapturingDecidable()
    {
        foreach ($this->children as $child) {
            if (!$child->isCapturingDecidable()) {
                return false;
            }   
        }
        return true;
    }

    /**
     * Return an of string represented children,
     * stopping descent when we hit a named node so the returned value
     * resembles the input rule.
     *
     */
    protected function stringMembers()
    {
        return array_map(function($child) {
            if ($child instanceof Reference) {
                return $child->asRhs();
            }
            return $child->name ?: $child->asRhs();
        }, $this->children);
    }
}
