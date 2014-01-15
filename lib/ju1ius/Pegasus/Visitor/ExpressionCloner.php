<?php

namespace ju1ius\Pegasus\Visitor;

use ju1ius\Pegasus\Expression;


class ExpressionCloner extends ExpressionVisitor
{
    public function leaveExpression(Expression $expr)
    {
        return clone $expr;
    }
}
