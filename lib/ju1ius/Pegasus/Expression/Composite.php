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
