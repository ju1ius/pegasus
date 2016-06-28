<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable 
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace ju1ius\Pegasus\Optimization;

use ju1ius\Pegasus\Expression;


class FlattenChoice extends Optimization
{
    use FlattenerTrait {
        FlattenerTrait::_appliesTo as __appliesTo;
    }

    public function _appliesTo(Expression $expr)
    {
        return $expr instanceof Expression\OneOf
            && $this->__appliesTo($expr)
        ;
    }

    public function isEligibleChild(Expression $child)
    {
        return $child instanceof Expression\OneOf;
    }
}
