<?php

namespace ju1ius\Pegasus\Expression;

use ju1ius\Pegasus\Parser\ParserInterface;
use ju1ius\Pegasus\Node;


/**
 * An expression which consumes nothing, even if it's contained expression succeeds.
 *
 **/
class Lookahead extends Wrapper
{
    public function asRhs()
    {
        return sprintf('&(%s)', $this->stringMembers());
    }
    
    public function match($text, $pos, ParserInterface $parser)
    {
        $node = $parser->apply($this->members[0], $pos);
        if($node) {
            return new Node\Lookahead($this, $text, $pos, $pos);
        }
    }
}
