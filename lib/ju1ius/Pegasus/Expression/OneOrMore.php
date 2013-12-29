<?php

namespace ju1ius\Pegasus\Expression;

use ju1ius\Pegasus\Expression\Quantifier;


/**
 * An expression wrapper like the + quantifier in regexes.
 **/
class OneOrMore extends Quantifier
{
    public function __construct($members, $name='')
    {
        parent::__construct($members, $name, 1, null);
    }

    public function asRhs()
    {
        return sprintf('(%s)+', $this->_stringMembers()[0]);
    }
}
