<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Grammar\Optimization;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Composite;

trait CompositeReducerTrait
{
    /**
     * Returns either a clone of an expression with the given new children,
     * or the sole child if there's only one new child.
     *
     * @param Composite $expr
     * @param Expression[] $children
     */
    protected function finishReduction(Composite $expr, array $children): Expression
    {
        if (\count($children) === 1) {
            $child = $children[0];
            $child->setName($expr->getName());

            return $child;
        }

        return $expr->withChildren(...$children);
    }
}
