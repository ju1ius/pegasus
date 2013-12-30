<?php

namespace ju1ius\Pegasus\Expression;

use ju1ius\Pegasus\Expression\Composite;
use ju1ius\Pegasus\Parser\ParserInterface;
use ju1ius\Pegasus\Node;


/**
 * An expression that succeeds only if the expression within it doesn't
 *
 * In any case, it never consumes any characters;
 * it's a negative lookahead.
 **/
class Not extends Composite
{
    public function asRhs()
    {
        return sprintf('!(%s)', $this->_stringMembers()[0]);
    }
    
    public function match($text, $pos, ParserInterface $parser)
    {
        $node = $parser->apply($this->members[0], $pos);
        if (!$node) {
            return new Node($this->name, $text, $pos, $pos);
        }
    }
}
