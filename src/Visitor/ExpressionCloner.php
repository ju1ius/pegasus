<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Visitor;

use ju1ius\Pegasus\Expression;

class ExpressionCloner extends ExpressionVisitor
{
    public function leaveExpression(Expression $expr, Composite $parent = null, $index = null)
    {
        return clone $expr;
    }
}
