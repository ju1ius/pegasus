<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Grammar;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Grammar;

/**
 * Generic GrammarVisitorInterface implementation.
 */
class GrammarVisitor implements GrammarVisitorInterface
{
    /**
     * @inheritDoc
     */
    public function beforeTraverse(Grammar $grammar): ?Grammar
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function afterTraverse(Grammar $grammar): ?Grammar
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function enterRule(Grammar $grammar, Expression $expr)
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function leaveRule(Grammar $grammar, Expression $expr)
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function enterExpression(Expression $expr, ?int $index = null, bool $isLast = false)
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function leaveExpression(Expression $expr, ?int $index = null, bool $isLast = false)
    {
        return null;
    }
}
