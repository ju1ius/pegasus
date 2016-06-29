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
 * An expression wrapper like the + quantifier in regexes.
 *
 **/
class OneOrMore extends Quantifier
{
    public function __construct($children, $name = '')
    {
        parent::__construct($children, 1, INF, $name);
    }

    public function asRhs()
    {
        return sprintf('(%s)+', $this->stringMembers());
    }
}
