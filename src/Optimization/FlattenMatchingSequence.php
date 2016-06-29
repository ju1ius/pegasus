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
use ju1ius\Pegasus\Expression\Sequence;

/**
 * Nested sequence expressions can be flattened without affecting how they match.
 */
class FlattenMatchingSequence extends FlatteningOptimization
{
    public function doAppliesTo(Expression $expr)
    {
        return $expr instanceof Sequence && parent::doAppliesTo($expr);
    }

    public function isEligibleChild(Expression $child)
    {
        return $child instanceof Sequence;
    }
}
