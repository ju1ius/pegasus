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
 * An expression that succeeds whether or not the contained one does.
 *
 * If the contained expression succeeds,
 * it goes ahead and consumes what it consumes.
 * Otherwise, it consumes nothing.
 */
class Optional extends Quantifier
{
    public function __construct(array $children = [], $name = '')
    {
        parent::__construct($children, 0, 1, $name);
    }

    public function __toString()
    {
        return sprintf('(%s)?', $this->stringMembers());
    }
}
