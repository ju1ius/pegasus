<?php

namespace ju1ius\Pegasus\Optimization;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Composite;

/**
 * @author ju1ius
 */
abstract class FlatteningOptimization extends Optimization
{
    protected function doAppliesTo(Expression $expr)
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
     * @return Composite
     */
    protected function doApply(Expression $expr)
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
        $class = get_class($expr);

        return new $class($children, $expr->name);
    }

    abstract protected function isEligibleChild(Expression $child);
}
