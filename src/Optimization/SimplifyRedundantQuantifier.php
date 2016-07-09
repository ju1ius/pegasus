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
use ju1ius\Pegasus\Expression\Quantifier;
use ju1ius\Pegasus\Expression\ZeroOrMore;

/**
 * A quantifier of a quantifier can be simplified to a simple quantifier.
 *
 * @author ju1ius <ju1ius@laposte.net>
 */
class SimplifyRedundantQuantifier extends Optimization
{
    /**
     * @inheritDoc
     */
    protected function doAppliesTo(Expression $expr, OptimizationContext $context)
    {
        return $context->isMatching()
            && $this->isSimpleQuantifier($expr)
            && $this->isSimpleQuantifier($expr[0]);
    }

    /**
     * @inheritDoc
     */
    protected function doApply(Expression $expr, OptimizationContext $context)
    {
        /** @var Quantifier $expr */
        /** @var Quantifier $child */
        $child = $expr[0];
        if (($expr->isZeroOrMore() && $child->isZeroOrMore())
            || ($expr->isOneOrMore() && $child->isOneOrMore())
            || ($expr->isOptional() && $child->isOptional())
        ) {
            $child = clone $child;
            $child->name = $expr->name;
            return $child;
        }

        return new ZeroOrMore($child[0], $expr->name);
    }

    private function isSimpleQuantifier(Expression $expr)
    {
        return $expr instanceof Quantifier
            && ($expr->isZeroOrMore()
                || $expr->isOneOrMore()
                || $expr->isOptional()
            );

    }
}