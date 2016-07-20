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
use ju1ius\Pegasus\Expression\Match;
use ju1ius\Pegasus\Expression\Quantifier;
use ju1ius\Pegasus\Expression\RegExp;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\Optimization;
use ju1ius\Pegasus\Grammar\OptimizationContext;

/**
 * A quantified Regexp match can be reduced to a single match.
 *
 * @author ju1ius <ju1ius@laposte.net>
 */
class CombineQuantifiedMatch extends Optimization
{
    /**
     * @inheritDoc
     */
    public function willPostProcessExpression(Expression $expr, OptimizationContext $context)
    {
        return $context->isMatching()
            && $expr instanceof Quantifier
            && ($expr[0] instanceof Match || $expr[0] instanceof RegExp);
    }

    /**
     * @inheritDoc
     */
    public function postProcessExpression(Expression $expr, OptimizationContext $context)
    {
        /** @var Quantifier $expr */
        /** @var Match $match */
        $match = $expr[0];

        return new Match(
            sprintf('(?>%s)%s', $match->getPattern(), $this->getQuantifier($expr)),
            $match->getFlags()
        );
    }

    /**
     * @param Quantifier $expr
     *
     * @return string
     */
    private function getQuantifier(Quantifier $expr)
    {
        if ($expr->isZeroOrMore()) {
            return '*';
        }
        if ($expr->isOneOrMore()) {
            return '+';
        }
        if ($expr->isOptional()) {
            return '?';
        }
        if ($expr->isExact()) {
            return sprintf('{%d}', $expr->getLowerBound());
        }

        return sprintf(
            '{%d,%s}',
            $expr->getLowerBound(),
            $expr->isUnbounded() ? '' : $expr->getUpperBound()
        );
    }
}
