<?php

namespace ju1ius\Pegasus\Expression;

use ju1ius\Pegasus\Expression;


/**
 * An abstract expression which contains other expressions 
 * 
 */
abstract class Composite extends Expression
{
    public $members;

    public function __construct(array $members=[], $name='')
    {
        parent::__construct($name);
        $this->members = $members;
    }

    /**
     * Return an iterable of my unicode-represented children,
     * stopping descent when we hit a named node so the returned value
     * resembles the input rule.
     *
     */
    protected function _stringMembers()
    {
        return array_map(function($member) {
            return $member->name ?: $member->asRhs();
        }, $this->members);
    }
}
