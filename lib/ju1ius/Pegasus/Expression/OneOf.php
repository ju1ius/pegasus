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
        return implode(' | ', $this->stringMembers());
    }

    public function isCapturingDecidable()
    {
        $capturing_children = 0;
        foreach ($this->members as $child) {
            if (!$child->isCapturingDecidable()) {
                return false;
            }
            if ($child->isCapturing()) {
                $capturing_children++;
            }   
        }
        return 0 === $capturing_children
            || $capturing_children === count($this->members)
        ;
    }
    
    public function match($text, $pos, ParserInterface $parser)
    {
        foreach ($this->members as $member) {
            $node = $parser->apply($member, $pos);
            if($node) {
                // Wrap the succeeding child in a node representing the OneOf
                return new Node\OneOf($this, $text, $pos, $node->end, [$node]);
            }
        }
    }
}
