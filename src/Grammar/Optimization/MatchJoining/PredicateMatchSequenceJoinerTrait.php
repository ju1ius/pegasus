<?php
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Grammar\Optimization\MatchJoining;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Decorator\Assert;
use ju1ius\Pegasus\Expression\Composite;
use ju1ius\Pegasus\Expression\Terminal\EOF;
use ju1ius\Pegasus\Expression\Terminal\Match;
use ju1ius\Pegasus\Expression\Decorator\Not;
use ju1ius\Pegasus\Expression\Combinator\Sequence;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\OptimizationContext;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
trait PredicateMatchSequenceJoinerTrait
{
    /**
     * @inheritDoc
     */
    public function willPostProcessExpression(Expression $expr, OptimizationContext $context)
    {
        return $expr instanceof Sequence && $this->someEligiblePairs($expr);
    }

    /**
     * @param Composite|Expression[] $children
     *
     * @return bool
     */
    abstract protected function someEligiblePairs($children);

    /**
     * @inheritDoc
     */
    protected function joinPatterns(array $patterns)
    {
        return implode('', $patterns);
    }

    /**
     * @inheritDoc
     */
    protected function prepareBarePattern(Expression $child)
    {
        if ($child instanceof Match) {
            return sprintf('(?>%s)', $child->getPattern());
        }
        if ($child instanceof Assert) {
            return sprintf('(?=%s)', $child[0]->getPattern());
        }
        if ($child instanceof Not) {
            return sprintf('(?!%s)', $child[0]->getPattern());
        }
        if ($child instanceof EOF) {
            return '\z';
        }
    }
}
