<?php declare(strict_types=1);
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
use ju1ius\Pegasus\Expression\Combinator\Sequence;
use ju1ius\Pegasus\Expression\Composite;
use ju1ius\Pegasus\Expression\Decorator\Assert;
use ju1ius\Pegasus\Expression\Decorator\Not;
use ju1ius\Pegasus\Expression\Terminal\EOF;
use ju1ius\Pegasus\Expression\Terminal\Literal;
use ju1ius\Pegasus\Expression\Terminal\Match;
use ju1ius\Pegasus\Grammar\OptimizationContext;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
trait PredicateMatchSequenceJoinerTrait
{
    /**
     * @inheritDoc
     */
    public function willPostProcessExpression(Expression $expr, OptimizationContext $context): bool
    {
        return $expr instanceof Sequence && $this->someEligiblePairs($expr);
    }

    /**
     * @param Composite|Expression[] $children
     *
     * @return bool
     */
    abstract protected function someEligiblePairs($children): bool;

    /**
     * @param string[] $patterns
     * @return string
     */
    protected function joinPatterns(array $patterns): string
    {
        return implode('', $patterns);
    }

    /**
     * @param Expression $child
     * @return null|string
     */
    protected function prepareBarePattern(Expression $child): string
    {
        if ($child instanceof Match || $child instanceof Literal) {
            $pattern = $this->manipulator->patternFor($child);

            return $this->manipulator->atomic($pattern);
        }
        if ($child instanceof Assert) {
            $pattern = $this->manipulator->patternFor($child[0]);

            return $this->manipulator->positiveLookahead($pattern);
        }
        if ($child instanceof Not) {
            $pattern = $this->manipulator->patternFor($child[0]);

            return $this->manipulator->negativeLookahead($pattern);
        }
        if ($child instanceof EOF) {
            return '\z';
        }
    }
}
