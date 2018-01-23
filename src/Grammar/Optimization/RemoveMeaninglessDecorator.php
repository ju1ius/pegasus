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
use ju1ius\Pegasus\Expression\Decorator\Ignore;
use ju1ius\Pegasus\Expression\Decorator\Token;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\Optimization;
use ju1ius\Pegasus\Grammar\OptimizationContext;

/**
 * `Token` and `Ignore` decorators only have meaning in a capturing context.
 *
 * @author ju1ius <ju1ius@laposte.net>
 */
class RemoveMeaninglessDecorator extends Optimization
{
    /**
     * @inheritDoc
     */
    public function willPostProcessExpression(Expression $expr, OptimizationContext $context): bool
    {
        return $context->isMatching() && (
            $expr instanceof Token
            || $expr instanceof Ignore
        );
    }

    /**
     * @inheritDoc
     */
    public function postProcessExpression(Expression $expr, OptimizationContext $context): ?Expression
    {
        return $expr[0];
    }
}
