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


trait FlattenerTrait
{
    protected function _appliesTo(Expression $expr)
    {
        foreach ($expr->children as $child) {
            if ($this->isEligibleChild($child)) {
                return true;
            }
        }
        return false;
    }

    protected function _apply(Expression $expr)
    {
        $children = [];
        foreach ($expr->children as $child) {
            if ($this->isEligibleChild($child)) {
                $children = array_merge($children, $child->children);
            } else {
                $children[] = $child;
            }
        }
        $class = get_class($expr);
        $new_expr = new $class($children, $expr->name);

        return $new_expr;
    }
}
