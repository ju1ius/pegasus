<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable 
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


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
