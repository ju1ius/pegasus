<?php

namespace ju1ius\Pegasus\Expression;

use ju1ius\Pegasus\Parser\ParserInterface;
use ju1ius\Pegasus\Node;


/**
 * An expression that succeeds only if the expression within it doesn't
 *
 * In any case, it never consumes any characters;
 * it's a negative lookahead.
 **/
class Not extends Wrapper
{
    public function asRhs()
    {
        return sprintf('!(%s)', $this->stringMembers());
    }

    public function isCapturing()
    {
        return false;
    }

    public function isCapturingDecidable()
    {
        return true;
    }
    
    public function match($text, $pos, ParserInterface $parser)
    {
        $node = $parser->apply($this->members[0], $pos);
        if (!$node) {
            return new Node\Not($this, $text, $pos, $pos);
        }
    }
}
