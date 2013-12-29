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

    protected function _uncachedMatch($text, $pos=0, array &$cache=null, ParseError $error=null, \SplStack $stack)
    {
        return new Node($this->name, $text, $pos, $pos);
    }
}
