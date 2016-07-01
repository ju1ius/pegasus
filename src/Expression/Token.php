<?php

namespace ju1ius\Pegasus\Expression;

use ju1ius\Pegasus\Node;
use ju1ius\Pegasus\Parser\ParserInterface;

/**
 * @author ju1ius
 */
class Token extends Decorator
{
    public function match($text, $pos, ParserInterface $parser)
    {
        if ($node = $parser->apply($this->children[0], $pos)) {
            return new Node\Token($this, $text, $node->start, $node->end);
        }
    }

    public function asRightHandSide()
    {
        return sprintf('@(%s)', $this->children[0]->asRightHandSide());
    }
}
