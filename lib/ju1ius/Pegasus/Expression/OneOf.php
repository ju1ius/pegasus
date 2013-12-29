<?php

namespace ju1ius\Pegasus\Expression;

use ju1ius\Pegasus\Expression\Composite;
use ju1ius\Pegasus\Exception\ParseError;
use ju1ius\Pegasus\Node;


/**
 * A series of expressions, one of which must match
 *
 * Expressions are tested in order from first to last.
 * The first to succeed wins.
 */
class OneOf extends Composite
{
    public function asRhs()
    {
        return implode(' | ', $this->_stringMembers());
    }
    
    protected function _uncachedMatch($text, $pos=0, array &$cache=null, ParseError $error=null, \SplStack $stack)
    {
        foreach ($this->members as $member) {
            $node = $member->_match($text, $pos, $cache, $error, $stack);
            if($node) {
                // Wrap the succeeding child in a node representing the OneOf
                return new Node($this->name, $text, $pos, $node->end, [$node]);
            }
        }
    }
}
