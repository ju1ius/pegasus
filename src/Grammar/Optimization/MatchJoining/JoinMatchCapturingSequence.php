<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Grammar\Optimization\MatchJoining;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Combinator\Sequence;
use ju1ius\Pegasus\Expression\Composite;
use ju1ius\Pegasus\Expression\Decorator\Ignore;
use ju1ius\Pegasus\Expression\Terminal\GroupMatch;
use ju1ius\Pegasus\Expression\Terminal\Literal;
use ju1ius\Pegasus\Expression\Terminal\NonCapturingRegExp;
use ju1ius\Pegasus\Grammar\OptimizationContext;
use ju1ius\Pegasus\Utils\Iter;

/**
 * @todo handle literals
 */
final class JoinMatchCapturingSequence extends MatchJoiningOptimization
{
    public function willPostProcessExpression(Expression $expr, OptimizationContext $context): bool
    {
        return $context->isCapturing()
            && $expr instanceof Sequence
            && !$this->disqualifyingCaptures($expr)
            && Iter::someConsecutive($this->isEligibleChild(...), 2, $expr);
    }

    protected function isEligibleChild(Expression $child): bool
    {
        return parent::isEligibleChild($child)
            || ($child instanceof GroupMatch && $child->getCaptureCount() === 1)
            || ($child instanceof Ignore && parent::isEligibleChild($child[0]));
    }

    protected function createPatterns(array $matches): array
    {
        $groupCount = 0;
        $patterns = array_map(function ($expr) use(&$groupCount) {
            if ($expr instanceof NonCapturingRegExp || $expr instanceof Literal) {
                $groupCount++;
                return sprintf('(%s)', $this->manipulator->patternFor($expr));
            }
            if ($expr instanceof GroupMatch) {
                $groupCount += $expr->getCaptureCount();
                $pattern = $this->manipulator->patternFor($expr);

                return $this->manipulator->atomic($pattern);
            }
            if ($expr instanceof Ignore) {
                $pattern = $this->manipulator->patternFor($expr[0]);

                return $this->manipulator->atomic($pattern);
            }
        }, $matches);

        return ['patterns' => $patterns, 'group_count' => $groupCount];
    }

    protected function joinPatterns(array $matchInfo): array
    {
        $matchInfo['pattern'] = implode('', $matchInfo['patterns']);

        return $matchInfo;
    }

    protected function createMatch($matchInfo): GroupMatch|Ignore
    {
        $match = new NonCapturingRegExp($matchInfo['pattern']);
        if (!$matchInfo['group_count']) {
            return new Ignore($match);
        }

        return new GroupMatch($match, $matchInfo['group_count']);
    }

    private function disqualifyingCaptures(Composite $expr): bool
    {
        return (
            $expr->some(fn(Expression $child) => $child->isCapturing() && $this->isEligibleChild($child))
            && $expr->some($this->isCaptureIncompatible(...))
        );
    }

    private function isCaptureIncompatible(Expression $child): bool
    {
        return $child->isCapturing() && !$this->isEligibleChild($child);
    }
}
