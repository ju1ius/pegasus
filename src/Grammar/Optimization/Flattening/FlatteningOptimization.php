<?php

namespace ju1ius\Pegasus\Grammar\Optimization\Flattening;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Composite;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\Optimization;
use ju1ius\Pegasus\Grammar\OptimizationContext;

/**
 * @author ju1ius
 */
abstract class FlatteningOptimization extends Optimization
{
    public function willPostProcessExpression(Expression $expr, OptimizationContext $context)
    {
        return $expr instanceof Composite && $expr->some(function (Expression $child) {
            return $this->isEligibleChild($child);
        });
    }

    /**
     * @inheritdoc
     */
    public function postProcessExpression(Expression $expr, OptimizationContext $context)
    {
        /** @var Composite $expr */
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
