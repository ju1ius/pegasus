<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Grammar\Optimization\MatchJoining;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Combinator\OneOf;
use ju1ius\Pegasus\Grammar\OptimizationContext;
use ju1ius\Pegasus\Utils\Iter;

final class JoinMatchChoice extends MatchJoiningOptimization
{
    public function willPostProcessExpression(Expression $expr, OptimizationContext $context): bool
    {
        return $expr instanceof OneOf
            && Iter::someConsecutive($this->isEligibleChild(...), 2, $expr);
    }

    protected function joinPatterns(array $patterns): array|string
    {
        return implode('|', $patterns);
    }
}
