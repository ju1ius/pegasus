<?php

namespace ju1ius\Pegasus\Expression;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Exception\ParseError;
use ju1ius\Pegasus\Node;


/**
 * The empty string
 *
 * Always matches without consuming any input.
 **/
class Epsilon extends Expression
{
    public function asRhs()
    {
        return 'E';
    }

    public function match($text, $pos, $parser)
    {
        return new Node($this->name, $text, $pos, $pos);
    }
}
