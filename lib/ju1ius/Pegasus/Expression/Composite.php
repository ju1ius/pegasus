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
    public $members;

    public function __construct(array $members=[], $name='')
    {
        parent::__construct($name);
        $this->members = array_values($members);
    }

    public function equals(Expression $other)
    {
        if (!parent::equals($other)) {
            return false;
        }
        foreach ($this->members as $i => $member) {
            if (!isset($other->members[$i])) {
                return false;
            }
            if (!$member->equals($other->members[$i])) {
                return false;
            }
        }
        return true;
    }

    public function isCapturing()
    {
        foreach ($this->members as $child) {
            if ($child->isCapturing()) {
                return true;
            }   
        }
        return false;
    }

    public function isCapturingDecidable()
    {
        foreach ($this->members as $child) {
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
        return array_map(function($member) {
            if ($member instanceof Reference) {
                return $member->asRhs();
            }
            return $member->name ?: $member->asRhs();
        }, $this->members);
    }
}
