<?php

namespace ju1ius\Pegasus\Expression;

use ju1ius\Pegasus\Expression\Composite;
use ju1ius\Pegasus\Exception\ParseError;
use ju1ius\Pegasus\Node;

/**
 * A series of expressions that must match contiguous, ordered pieces of the text
 *
 * In other words, it's a concatenation operator: each piece has to match,
 * one after another 
 */
class Sequence extends Composite
{
    public function asRhs()
    {
        return implode(' ', $this->_stringMembers());
    }
    
    protected function _uncachedMatch($text, $pos=0, array &$cache=null, ParseError $error=null, \SplStack $stack)
    {
        $new_pos = $pos;
        $seq_len = 0;
        $children = [];
        foreach ($this->members as $member) {
            $node = $member->_match($text, $new_pos, $cache, $error, $stack);
            if (!$node) return;
            $children[] = $node;
            $len = $node->end - $node->start;
            $new_pos += $len;
            $seq_len += $len;
        }
        return new Node($this->name, $text, $pos, $pos + $seq_len, $children);
    }
}
