<?php

namespace ju1ius\Pegasus\Expression;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Node;


/**
 * Matches if there's nothing left to consume.
 */
class EOF extends Expression
{
    public function __construct()
    {
        parent::__construct('EOF');
    }
    
    public function match($text, $pos, $parser)
    {
        if (!isset($text[$pos])) {
            return new Node('EOF', $text, $pos, $pos);
        }
    }

    public function asRhs()
    {
        return 'EOF';
    }
}
