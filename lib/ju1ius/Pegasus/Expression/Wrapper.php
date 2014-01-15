<?php

namespace ju1ius\Pegasus\Expression;


/**
 * A composite expression which contains only one sub-expression. 
 * ATM it does nothing more than Composite,
 * and is here only for easier type-checking in visitors.
 * 
 */
abstract class Wrapper extends Composite
{
    public function stringMembers()
    {
        return parent::stringMembers()[0];
    }
}
