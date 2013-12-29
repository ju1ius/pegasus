<?php

namespace ju1ius\Pegasus\Expression;

use ju1ius\Pegasus\Expression\Composite;
use ju1ius\Pegasus\Exception\ParseError;
use ju1ius\Pegasus\Node;

/**
 * An expression which consumes nothing, even if it's contained expression succeeds.
 *
 **/
class Lookahead extends Composite
{
    public function asRhs()
    {
        return sprintf('&(%s)', $this->_stringMembers()[0]);
    }
    
    protected function _uncachedMatch($text, $pos=0, array &$cache=null, ParseError $error=null, \SplStack $stack)
    {
        $node = $this->members[0]->_match($text, $pos, $cache, $error, $stack);
        if($node) {
            return new Node($this->name, $text, $pos, $pos);
        }
    }
}
