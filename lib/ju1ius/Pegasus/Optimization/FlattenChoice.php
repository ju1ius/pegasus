<?php

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
