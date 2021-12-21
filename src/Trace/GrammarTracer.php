<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Trace;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Decorator\Trace;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\GrammarVisitor;

final class GrammarTracer extends GrammarVisitor
{
    private bool $enable;

    public function __construct(bool $enable = true)
    {
        $this->enable = $enable;
    }

    public function enterExpression(Expression $expr, ?int $index = null, bool $isLast = false): ?Expression
    {
        if ($expr instanceof Trace) {
            // Always remove the existing Trace to prevent double-wrapping
            return $expr[0];
        }

        return null;
    }

    public function leaveExpression(Expression $expr, ?int $index = null, bool $isLast = false): ?Expression
    {
        if ($this->enable) {
            return new Trace($expr);
        }

        return null;
    }
}
