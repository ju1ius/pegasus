<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Grammar\Optimization\MatchJoining;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Terminal\Literal;
use ju1ius\Pegasus\Expression\Terminal\NonCapturingRegExp;

abstract class PredicateBareMatchJoiningOptimization extends PredicateMatchJoiningOptimization
{
    abstract protected function prepareBarePattern(Expression $child): string;

    protected function preparePattern(Expression $child): string
    {
        return $this->prepareBarePattern($child);
    }

    protected function isEligibleMatch(Expression $expr): bool
    {
        return $expr instanceof NonCapturingRegExp || $expr instanceof Literal;
    }
}
