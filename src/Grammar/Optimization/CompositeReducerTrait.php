<?php declare(strict_types=1);
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Grammar\Optimization;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Composite;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
trait CompositeReducerTrait
{
    /**
     * Returns either a clone of an expression with the given new children,
     * or the sole child if there's only one new child.
     *
     * @param Composite    $expr
     * @param Expression[] $children
     *
     * @return Composite|Expression
     */
    protected function finishReduction(Composite $expr, array $children)
    {
        if (count($children) === 1) {
            $child = $children[0];
            $child->setName($expr->getName());

            return $child;
        }

        return $expr->withChildren(...$children);
    }
}
