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
use ju1ius\Pegasus\Grammar\OptimizationContext;
use ju1ius\Pegasus\Utils\Iter;

/**
 * @todo handle literals
 *
 * @author ju1ius <ju1ius@laposte.net>
 */
final class JoinMatchMatchingSequence extends MatchJoiningOptimization
{
    /**
     * @inheritDoc
     *
     * @todo Should we handle NamedSequences too ?
     */
    public function willPostProcessExpression(Expression $expr, OptimizationContext $context): bool
    {
        return $context->isMatching()
            && $expr instanceof Sequence
            && Iter::someConsecutive(function ($child) {
                return $this->isEligibleChild($child);
            }, 2, $expr);
    }

    /**
     * @param Expression $expr
     *
     * @return string
     */
    protected function createPattern(Expression $expr): string
    {
        $pattern = $this->manipulator->patternFor($expr);

        return $this->manipulator->atomic($pattern);
    }
}
