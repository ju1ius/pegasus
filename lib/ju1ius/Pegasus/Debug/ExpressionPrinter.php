<?php

namespace ju1ius\Pegasus\Debug;

use ju1ius\Pegasus\Visitor\ExpressionVisitor;
use ju1ius\Pegasus\Expression;


class ExpressionPrinter extends ExpressionVisitor
{
    public function beforeTraverse(Expression $expr)
    {
        $this->level = 0;
    }

    public function enterExpression(Expression $expr)
    {
        echo $this->formatExpression($expr);
        if ($expr instanceof Expression\Composite) {
            $this->level++;
        }
    }

    public function leaveExpression(Expression $expr)
    {
        if ($expr instanceof Expression\Composite) {
            $this->level--;
        }
    }

    protected function formatExpression(Expression $expr)
    {
        return sprintf(
            "%s+<%s(%s) = %s>\n",
            $this->indent(),
            $expr->name ? $expr->name . ' ' : '',
            str_replace('ju1ius\Pegasus\Expression\\', '', get_class($expr)),
            $expr->asRhs()
        );
    }

    protected function indent()
    {
        return str_repeat('+---', $this->level);
    }
}
