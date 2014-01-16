<?php

namespace ju1ius\Pegasus\Expression;

use ju1ius\Pegasus\Expression\Quantifier;


/**
 * An expression wrapper like the * quantifier in regexes.
 *
 **/
class ZeroOrMore extends Quantifier
{
    public function __construct(array $children=[], $name='')
    {
        parent::__construct($children, 0, INF, $name);
    }
    
    public function asRhs()
    {
        return sprintf('(%s)*', $this->stringMembers());
    }
}
