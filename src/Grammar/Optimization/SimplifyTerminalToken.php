<?php declare(strict_types=1);
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Grammar\Optimization;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Decorator\Token;
use ju1ius\Pegasus\Expression\Terminal;
use ju1ius\Pegasus\Grammar\Optimization;
use ju1ius\Pegasus\Grammar\OptimizationContext;

/**
 * A token wrapping a terminal expression is redundant.
 *
 * @author ju1ius <ju1ius@laposte.net>
 */
class SimplifyTerminalToken extends Optimization
{
    public function postProcessExpression(Expression $expr, OptimizationContext $context): ?Expression
    {
        return $expr[0];
    }

    /**
     * @inheritDoc
     */
    public function willPostProcessExpression(Expression $expr, OptimizationContext $context): bool
    {
        return $expr instanceof Token && (
            $expr[0] instanceof Terminal
            || $expr[0] instanceof Token
        );
    }
}
