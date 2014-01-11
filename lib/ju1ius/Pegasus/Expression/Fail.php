<?php

namespace ju1ius\Pegasus\Expression;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Parser\ParserInterface;
use ju1ius\Pegasus\Node;


/**
 * An expression that always fail without consuming any input. 
 *
 * This can be used to signal malformed input. 
 **/
class Fail extends Expression
{
    public function asRhs()
    {
		return 'FAIL';
    }

    public function match($text, $pos, ParserInterface $parser)
    {
        return null;
    }
}
