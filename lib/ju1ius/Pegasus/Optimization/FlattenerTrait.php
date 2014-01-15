<?php

namespace ju1ius\Pegasus\Optimization;

use ju1ius\Pegasus\Expression;


trait FlattenerTrait
{
    protected function _appliesTo(Expression $expr)
    {
        foreach ($expr->members as $child) {
            if ($this->isEligibleChild($child)) {
                return true;
            }
        }
        return false;
    }

    protected function _apply(Expression $expr)
    {
        $children = [];
        foreach ($expr->members as $child) {
            if ($this->isEligibleChild($child)) {
                $children = array_merge($children, $child->members);
            } else {
                $children[] = $child;
            }
        }
        $class = get_class($expr);
        $new_expr = new $class($children, $expr->name);

        return $new_expr;
    }
}
