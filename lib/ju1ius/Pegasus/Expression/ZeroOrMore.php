<?php

namespace ju1ius\Pegasus\Expression;

use ju1ius\Pegasus\Expression\Quantifier;


/**
 * An expression wrapper like the * quantifier in regexes 
 **/
class ZeroOrMore extends Quantifier
{
    public function __construct(array $members=[], $name='')
    {
        parent::__construct($members, $name, 0, null);
    }
    
    public function asRhs()
    {
        return sprintf('(%s)*', $this->_stringMembers()[0]);
    }
}
