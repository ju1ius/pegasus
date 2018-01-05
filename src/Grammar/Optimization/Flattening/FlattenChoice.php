<?php declare(strict_types=1);
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Grammar\Optimization\Flattening;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Combinator\OneOf;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\Optimization\Flattening\FlatteningOptimization;
use ju1ius\Pegasus\Grammar\OptimizationContext;

final class FlattenChoice extends FlatteningOptimization
{
    public function willPostProcessExpression(Expression $expr, OptimizationContext $context): bool
    {
        return $expr instanceof OneOf && parent::willPostProcessExpression($expr, $context);
    }

    public function isEligibleChild(Expression $child)
    {
        return $child instanceof OneOf;
    }
}
