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

use ju1ius\Pegasus\Expression;

/**
 * An expression wrapper like the * quantifier in regexes.
 *
 */
class ZeroOrMore extends Quantifier
{
    public function __construct(Expression $child = null, $name = '')
    {
        parent::__construct($child, 0, INF, $name);
    }

    public function __toString()
    {
        return sprintf('(%s)*', $this->stringChildren());
    }
}
