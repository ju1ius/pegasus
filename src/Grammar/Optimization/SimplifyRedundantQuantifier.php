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
use ju1ius\Pegasus\Expression\Quantifier;
use ju1ius\Pegasus\Expression\ZeroOrMore;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\Optimization;
use ju1ius\Pegasus\Grammar\OptimizationContext;

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
    public function willPostProcessExpression(Expression $expr, OptimizationContext $context)
    {
        return $context->isMatching()
            && $this->isSimpleQuantifier($expr)
            && $this->isSimpleQuantifier($expr[0]);
    }

    /**
     * @inheritDoc
     */
    public function postProcessExpression(Expression $expr, OptimizationContext $context)
    {
        /** @var Quantifier $expr */
        /** @var Quantifier $child */
        $child = $expr[0];
        if (($expr->isZeroOrMore() && $child->isZeroOrMore())
            || ($expr->isOneOrMore() && $child->isOneOrMore())
            || ($expr->isOptional() && $child->isOptional())
        ) {
            $child = clone $child;
            $child->setName($expr->getName());

            return $child;
        }

        return new ZeroOrMore($child[0], $expr->getName());
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
