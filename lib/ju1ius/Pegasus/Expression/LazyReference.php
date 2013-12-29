<?php

namespace ju1ius\Pegasus\Expression;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Exception\ParseError;

/**
 * A lazy reference to a rule,
 * which we resolve after grokking all the rules
 */
class LazyReference extends Expression
{
    public function __construct($identifier)
    {
        $this->identifier = $identifier;
        parent::__construct();
    }
    
    public function asRhs()
    {
        return "<LazyReference to {$this->identifier}>";
    }

    public function match($text, $pos, $parser)
    {
        return null;
    }
}
