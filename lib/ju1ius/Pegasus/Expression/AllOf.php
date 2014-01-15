<?php

namespace ju1ius\Pegasus\Expression;

use ju1ius\Pegasus\Expression\Composite;
use ju1ius\Pegasus\Parser\ParserInterface;
use ju1ius\Pegasus\Node;

/**
 * A series of expressions, each of which must succeed from the current position.
 *
 * The returned node is from the last member.
 * If you like, you can think of the preceding members as lookaheads. 
 **/
class AllOf extends Composite
{
    public function match($text, $pos, ParserInterface $parser)
    {
        foreach ($this->members as $member) {
            $node = $parser->apply($this, $pos);
            if(!$node) return;
        }
        return new Node\AllOf($this, $text, $pos, $node->end, [$node]);
    }
}
