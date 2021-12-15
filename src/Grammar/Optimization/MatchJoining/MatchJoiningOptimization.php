<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Grammar\Optimization\MatchJoining;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Composite;
use ju1ius\Pegasus\Expression\Decorator\Ignore;
use ju1ius\Pegasus\Expression\Terminal\GroupMatch;
use ju1ius\Pegasus\Expression\Terminal\Literal;
use ju1ius\Pegasus\Expression\Terminal\NonCapturingRegExp;
use ju1ius\Pegasus\Grammar\Optimization\CompositeReducerTrait;
use ju1ius\Pegasus\Grammar\Optimization\RegExpOptimization;
use ju1ius\Pegasus\Grammar\OptimizationContext;
use ju1ius\Pegasus\Utils\Iter;

abstract class MatchJoiningOptimization extends RegExpOptimization
{
    use CompositeReducerTrait;

    public function willPostProcessExpression(Expression $expr, OptimizationContext $context): bool
    {
        assert($expr instanceof Composite);
        return Iter::someConsecutive($this->isEligibleChild(...), 2, $expr);
    }

    public function postProcessExpression(Expression $expr, OptimizationContext $context): ?Expression
    {
        assert($expr instanceof Composite);
        $newChildren = [];
        $matches = [];
        foreach ($expr as $i => $child) {
            if ($this->isEligibleChild($child)) {
                $matches[] = $child;
            } else {
                if ($matches) {
                    $newChildren[] = $this->joinMatches($matches);
                }
                $matches = [];
                $newChildren[] = $child;
            }
        }
        if ($matches) {
            $newChildren[] = $this->joinMatches($matches);
        }

        return $this->finishReduction($expr, $newChildren);
    }

    /**
     * @todo handle non mergeable flags
     */
    protected function isEligibleChild(Expression $child): bool
    {
        return $child instanceof NonCapturingRegExp || $child instanceof Literal;
    }

    /**
     * @param NonCapturingRegExp[] $matches
     */
    protected function joinMatches(array $matches): GroupMatch|NonCapturingRegExp|Ignore
    {
        $patterns = $this->createPatterns($matches);
        $pattern = $this->joinPatterns($patterns);

        return $this->createMatch($pattern);
    }

    /**
     * @param NonCapturingRegExp[] $matches
     * @return string[]
     */
    protected function createPatterns(array $matches): array
    {
        return array_map($this->createPattern(...), $matches);
    }

    /**
     * @param string[] $patterns
     */
    protected function joinPatterns(array $patterns): array|string
    {
        return implode('', $patterns);
    }

    protected function createPattern(Expression $expr): string
    {
        return $this->manipulator->patternFor($expr);
    }

    protected function createMatch(string $pattern): GroupMatch|NonCapturingRegExp|Ignore
    {
        return new NonCapturingRegExp($pattern);
    }
}
