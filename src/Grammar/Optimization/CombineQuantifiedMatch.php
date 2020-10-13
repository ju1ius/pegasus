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
use ju1ius\Pegasus\Expression\Decorator\Quantifier;
use ju1ius\Pegasus\Expression\Terminal\Literal;
use ju1ius\Pegasus\Expression\Terminal\Match;
use ju1ius\Pegasus\Expression\Terminal\RegExp;
use ju1ius\Pegasus\Grammar\OptimizationContext;

/**
 * A quantified Regexp match can be reduced to a single match.
 *
 * @author ju1ius <ju1ius@laposte.net>
 */
class CombineQuantifiedMatch extends RegExpOptimization
{
    /**
     * @inheritDoc
     */
    public function willPostProcessExpression(Expression $expr, OptimizationContext $context): bool
    {
        return $context->isMatching()
            && $expr instanceof Quantifier
            && ($expr[0] instanceof Literal
                || $expr[0] instanceof Match
                || $expr[0] instanceof RegExp);
    }

    /**
     * @inheritDoc
     */
    public function postProcessExpression(Expression $expr, OptimizationContext $context): ?Expression
    {
        /** @var Quantifier $expr */
        $quantifier = $this->manipulator->patternFor($expr);
        $pattern = $this->manipulator->atomic($this->manipulator->patternFor($expr[0]));

        return new Match(sprintf('%s%s', $pattern, $quantifier));
    }
}
