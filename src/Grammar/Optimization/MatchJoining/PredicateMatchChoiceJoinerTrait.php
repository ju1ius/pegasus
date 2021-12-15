<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Grammar\Optimization\MatchJoining;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Combinator\OneOf;
use ju1ius\Pegasus\Expression\Composite;
use ju1ius\Pegasus\Expression\Decorator\Assert;
use ju1ius\Pegasus\Expression\Decorator\Not;
use ju1ius\Pegasus\Expression\Terminal\EOF;
use ju1ius\Pegasus\Expression\Terminal\Literal;
use ju1ius\Pegasus\Expression\Terminal\NonCapturingRegExp;
use ju1ius\Pegasus\Grammar\OptimizationContext;

trait PredicateMatchChoiceJoinerTrait
{
    public function willPostProcessExpression(Expression $expr, OptimizationContext $context): bool
    {
        return $expr instanceof OneOf && $this->someEligiblePairs($expr);
    }

    abstract protected function someEligiblePairs(Composite $children): bool;

    protected function joinPatterns(array $patterns): string
    {
        return implode('|', $patterns);
    }

    protected function prepareBarePattern(Expression $child): string
    {
        if ($child instanceof NonCapturingRegExp || $child instanceof Literal) {
            return $this->manipulator->patternFor($child);
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

        throw new \LogicException('Should not have reached here !');
    }
}
