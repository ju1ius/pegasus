<?php declare(strict_types=1);
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Expression;

use ju1ius\Pegasus\Expression;

/**
 * Generic ExpressionVisitorInterface implementation.
 *
 * @codeCoverageIgnore
 */
abstract class ExpressionVisitor implements ExpressionVisitorInterface
{
    /**
     * @inheritDoc
     */
    public function beforeTraverse(Expression $expr)
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

    /**
     * @inheritDoc
     */
    public function afterTraverse(Expression $expr)
    {
        return null;
    }
}
