<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Grammar\Optimization\MatchJoining;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Combinator\Sequence;
use ju1ius\Pegasus\Grammar\OptimizationContext;
use ju1ius\Pegasus\Utils\Iter;

/**
 * @todo handle literals
 */
final class JoinMatchMatchingSequence extends MatchJoiningOptimization
{
    /**
     * @todo Should we handle NamedSequences too ?
     */
    public function willPostProcessExpression(Expression $expr, OptimizationContext $context): bool
    {
        return $context->isMatching()
            && $expr instanceof Sequence
            && Iter::someConsecutive($this->isEligibleChild(...), 2, $expr);
    }

    /**
     * @param Expression $expr
     */
    protected function createPattern(Expression $expr): string
    {
        $pattern = $this->manipulator->patternFor($expr);

        return $this->manipulator->atomic($pattern);
    }
}
