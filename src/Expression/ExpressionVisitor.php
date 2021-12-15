<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Expression;

use ju1ius\Pegasus\Expression;

/**
 * Generic ExpressionVisitorInterface implementation.
 *
 * @codeCoverageIgnore
 */
abstract class ExpressionVisitor implements ExpressionVisitorInterface
{
    public function beforeTraverse(Expression $expr)
    {
        return null;
    }

    public function enterExpression(Expression $expr, ?int $index = null, bool $isLast = false)
    {
        return null;
    }

    public function leaveExpression(Expression $expr, ?int $index = null, bool $isLast = false)
    {
        return null;
    }

    public function afterTraverse(Expression $expr)
    {
        return null;
    }
}
