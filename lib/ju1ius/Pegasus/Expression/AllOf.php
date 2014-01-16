<?php

namespace ju1ius\Pegasus\Expression;

use ju1ius\Pegasus\Expression\Composite;
use ju1ius\Pegasus\Parser\ParserInterface;
use ju1ius\Pegasus\Node;

/**
 * A series of expressions, each of which must succeed from the current position.
 *
 * The returned node is from the last child.
 * If you like, you can think of the preceding children as lookaheads. 
 **/
class AllOf extends Composite
{
    public function match($text, $pos, ParserInterface $parser)
    {
        foreach ($this->children as $child) {
            $node = $parser->apply($this, $pos);
            if(!$node) return;
        }
        return new Node\AllOf($this, $text, $pos, $node->end, [$node]);
    }
}
