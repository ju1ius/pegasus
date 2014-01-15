<?php

namespace ju1ius\Pegasus\Expression;

use ju1ius\Pegasus\Parser\ParserInterface;
use ju1ius\Pegasus\Node;


/**
 * Matches if there's nothing left to consume.
 */
class EOF extends Terminal
{
    public function __construct()
    {
        parent::__construct('EOF');
    }

    public function isCapturing()
    {
        return false;
    }
    
    public function match($text, $pos, ParserInterface $parser)
    {
        if (!isset($text[$pos])) {
            return new Node\EOF($this, $text, $pos, $pos);
        }
    }

    public function asRhs()
    {
        return 'EOF';
    }
}
