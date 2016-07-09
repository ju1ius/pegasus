<?php

namespace ju1ius\Pegasus\Optimization;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Composite;

/**
 * @author ju1ius
 */
abstract class FlatteningOptimization extends Optimization
{
    protected function doAppliesTo(Expression $expr, OptimizationContext $context)
    {
        if (!$expr instanceof Composite) {
            return false;
        }

        return $expr->some(function (Expression $child) {
            return $this->isEligibleChild($child);
        });
    }

    /**
     * @param Expression|Composite $expr
     *
     * @param OptimizationContext  $context
     *
     * @return Composite
     */
    protected function doApply(Expression $expr, OptimizationContext $context)
    {
        $children = [];
        foreach ($expr as $child) {
            if ($this->isEligibleChild($child)) {
                /** @var Composite $child */
                array_push($children, ...$child);
            } else {
                $children[] = $child;
            }
        }

        return $expr->withChildren(...$children);
    }

    abstract protected function isEligibleChild(Expression $child);
}
