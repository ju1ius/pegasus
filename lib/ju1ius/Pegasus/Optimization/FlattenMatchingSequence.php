<?php

namespace ju1ius\Pegasus\Optimization;

use ju1ius\Pegasus\Expression;


/**
 * Nested sequence expressions can be flattened without affecting how they match.
 *
 */
class FlattenMatchingSequence extends Optimization
{
    use FlattenerTrait {
        FlattenerTrait::_appliesTo as __appliesTo;
    }

    public function _appliesTo(Expression $expr)
    {
        return $expr instanceof Expression\Sequence
            && $this->__appliesTo($expr)
        ;
    }

    public function isEligibleChild(Expression $child)
    {
        return $child instanceof Expression\Sequence;
    }
}
