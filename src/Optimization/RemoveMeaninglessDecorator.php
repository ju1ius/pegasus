<?php
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Optimization;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Skip;
use ju1ius\Pegasus\Expression\Token;

/**
 * `Token` and `Skip` decorators only have meaning in a capturing context.
 *
 * @author ju1ius <ju1ius@laposte.net>
 */
class RemoveMeaninglessDecorator extends Optimization
{
    /**
     * @inheritDoc
     */
    protected function doAppliesTo(Expression $expr, OptimizationContext $context)
    {
        return $context->isMatching() && (
            $expr instanceof Token
            || $expr instanceof Skip
        );
    }

    /**
     * @inheritDoc
     */
    protected function doApply(Expression $expr, OptimizationContext $context)
    {
        return $expr[0];
    }
}
