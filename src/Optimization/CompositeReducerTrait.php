<?php
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Optimization;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Composite;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
trait CompositeReducerTrait
{
    /**
     * @param Composite    $expr
     * @param Expression[] $children
     *
     * @return Composite|Expression
     */
    protected function finishReducing(Composite $expr, array $children)
    {
        if (count($children) === 1) {
            $child = $children[0];
            $child->name = $expr->name;

            return $child;
        }

        return $expr->withChildren($children);
    }
}
