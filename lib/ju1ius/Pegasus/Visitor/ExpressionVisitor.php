<?php

namespace ju1ius\Pegasus\Visitor;

use ju1ius\Pegasus\Expression;


/**
 * @codeCoverageIgnore
 */
class ExpressionVisitor implements ExpressionVisitorInterface
{
    public function beforeTraverse(Expression $expr)
    {
        return null;
    }
    public function enterNode(Expression $expr)
    {
        return null;
    }
    public function leaveNode(Expression $expr)
    {
        return null;
    }
    public function afterTraverse(Expression $expr)
    {
        return null;
    }
}
