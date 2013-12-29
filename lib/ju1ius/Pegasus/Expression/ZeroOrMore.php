<?php

namespace ju1ius\Pegasus\Expression;

use ju1ius\Pegasus\Expression\Quantifier;

/**
 * An expression wrapper like the * quantifier in regexes 
 **/
class ZeroOrMore extends Quantifier
{
    public function __construct(array $members=[], $name='')
    {
        parent::__construct($members, $name, 0, null);
    }
    
    public function asRhs()
    {
        return sprintf('(%s)*', $this->_stringMembers()[0]);
    }
    
    /*
    protected function _uncachedMatch($text, $pos=0, $cache=null, $error=null)
    {
        $new_pos = $pos;
        $children = [];
        while(true) {
            $node = $this->members[0]->_match($text, $pos, $cache, $error);
            $length = $node->end - $node->start;
            if(!$node || !$length) {
                // Node was None or 0 length. 0 would otherwise loop infinitely.
                return new Node($this->name, $text, $pos, $new_pos, $children);
            }
            $children[] = $node;
            $new_pos += $length;
        }
    }
     */
}
