<?php

namespace ju1ius\Pegasus\Expression;

use ju1ius\Pegasus\Expression\Quantifier;


/**
 * An expression wrapper like the + quantifier in regexes.
 **/
class OneOrMore extends Quantifier
{
    public function __construct($members, $name='')
    {
        parent::__construct($members, $name, 1, null);
    }

    public function asRhs()
    {
        return sprintf('(%s)+', $this->_stringMembers()[0]);
    }
    
    /*
    protected function _uncachedMatch($text, $pos=0, $cache=null, $error=null)
    {
        $new_pos = $pos;
        $children = [];
        while(true) {
            $node = $this->members[0]->_match($text, $pos, $cache, $error);
            if(!$node) break;
            $children[] = $node;
            $length = $node->end - $node->start;
            if(!$length) break;
            $new_pos += $length;
        }
        if(count($children) >= $this->min) {
            return new Node($this->name, $text, $pos, $new_pos, $children);
        }
    }
    */
}
