<?php
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
use ju1ius\Pegasus\Expression\Terminal;
use ju1ius\Pegasus\Expression\Token;
use ju1ius\Pegasus\Grammar\Optimization;
use ju1ius\Pegasus\Grammar\OptimizationContext;

/**
 * A token wrapping a terminal expression is redundant.
 *
 * @author ju1ius <ju1ius@laposte.net>
 */
class SimplifyTerminalToken extends Optimization
{
    /**
     * @inheritDoc
     */
    protected function doApply(Expression $expr, OptimizationContext $context)
    {
        return $expr[0];
    }

    /**
     * @inheritDoc
     */
    protected function doAppliesTo(Expression $expr, OptimizationContext $context)
    {
        return $expr instanceof Token && (
            $expr[0] instanceof Terminal
            || $expr[0] instanceof Token
        );
    }
}
