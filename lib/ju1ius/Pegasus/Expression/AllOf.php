<?php

namespace ju1ius\Pegasus\Expression;

use ju1ius\Pegasus\Expression\Composite;
use ju1ius\Pegasus\Exception\ParseError;
use ju1ius\Pegasus\Node;

/**
 * A series of expressions, each of which must succeed from the current position.
 *
 * The returned node is from the last member.
 * If you like, you can think of the preceding members as lookaheads. 
 **/
class AllOf extends Composite
{
    public function match($text, $pos, $parser)
    {
        foreach ($this->members as $member) {
            $node = $parser->apply($this);
            if(!$node) return;
        }
        return new Node($this->name, $text, $pos, $node->end, [$node]);
    }
}
