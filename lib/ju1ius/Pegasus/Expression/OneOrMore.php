<?php

namespace ju1ius\Pegasus\Expression;

use ju1ius\Pegasus\Expression\Quantifier;


/**
 * An expression wrapper like the + quantifier in regexes.
 *
 **/
class OneOrMore extends Quantifier
{
    public function __construct($children, $name='')
    {
        parent::__construct($children, 1, INF, $name);
    }

    public function asRhs()
    {
        return sprintf('(%s)+', $this->stringMembers());
    }
}
