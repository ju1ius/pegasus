<?php

namespace ju1ius\Pegasus\Expression;

use ju1ius\Pegasus\Expression\Quantifier;

/**
 * An expression that succeeds whether or not the contained one does
 *
 * If the contained expression succeeds,
 * it goes ahead and consumes what it consumes.
 * Otherwise, it consumes nothing. 
 **/
class Optional extends Quantifier 
{   
    public function __construct(array $members=[], $name='')
    {
        parent::__construct($members, $name, 0, 1);
    }

    public function asRhs()
    {
        return sprintf('(%s)?', $this->_stringMembers()[0]);
    }
}
