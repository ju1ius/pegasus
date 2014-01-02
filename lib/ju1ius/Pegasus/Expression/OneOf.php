<?php

namespace ju1ius\Pegasus\Expression;

use ju1ius\Pegasus\Expression\Composite;
use ju1ius\Pegasus\Parser\ParserInterface;
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
    
    public function match($text, $pos, ParserInterface $parser)
    {
        foreach ($this->members as $member) {
            $node = $parser->apply($member, $pos);
            if($node) {
                // Wrap the succeeding child in a node representing the OneOf
                return Node::fromExpression($this, $text, $pos, $node->end, [$node]);
            }
        }
    }
}
