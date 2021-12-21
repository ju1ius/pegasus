<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Grammar;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Grammar;

/**
 * Generic GrammarVisitorInterface implementation.
 */
class GrammarVisitor implements GrammarVisitorInterface
{
    public function beforeTraverse(Grammar $grammar): ?Grammar
    {
        return null;
    }

    public function afterTraverse(Grammar $grammar): ?Grammar
    {
        return null;
    }

    public function enterRule(Grammar $grammar, Expression $expr): ?Expression
    {
        return null;
    }

    public function leaveRule(Grammar $grammar, Expression $expr): ?Expression
    {
        return null;
    }

    public function enterExpression(Expression $expr, ?int $index = null, bool $isLast = false): ?Expression
    {
        return null;
    }

    public function leaveExpression(Expression $expr, ?int $index = null, bool $isLast = false): ?Expression
    {
        return null;
    }
}
