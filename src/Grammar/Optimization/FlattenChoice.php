<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Grammar\Optimization;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\OneOf;
use ju1ius\Pegasus\Grammar\Optimization\Flattening\FlatteningOptimization;
use ju1ius\Pegasus\Grammar\OptimizationContext;

final class FlattenChoice extends FlatteningOptimization
{
    protected function doAppliesTo(Expression $expr, OptimizationContext $context)
    {
        return $expr instanceof OneOf && parent::doAppliesTo($expr, $context);
    }

    public function isEligibleChild(Expression $child)
    {
        return $child instanceof OneOf;
    }
}
