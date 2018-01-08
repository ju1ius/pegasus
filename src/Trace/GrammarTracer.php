<?php declare(strict_types=1);


namespace ju1ius\Pegasus\Trace;


use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Grammar\GrammarVisitor;


final class GrammarTracer extends GrammarVisitor
{
    public function leaveExpression(Expression $expr, ?int $index = null, bool $isLast = false)
    {
        return new Expression\Decorator\Trace($expr);
    }
}