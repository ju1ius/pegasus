<?php

namespace ju1ius\Pegasus\Expression;

use ju1ius\Pegasus\Parser\ParserInterface;
use ju1ius\Pegasus\Node;


/**
 * The empty string
 *
 * Always matches without consuming any input.
 **/
class Epsilon extends Terminal
{
    public function asRhs()
    {
        return '𝝴';
    }

    public function isCapturing()
    {
        return false;
    }

    public function match($text, $pos, ParserInterface $parser)
    {
        return new Node\Epsilon($this, $text, $pos, $pos);
    }
}
