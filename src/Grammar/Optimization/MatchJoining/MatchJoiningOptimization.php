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
use ju1ius\Pegasus\Expression\Composite;
use ju1ius\Pegasus\Expression\Terminal\Literal;
use ju1ius\Pegasus\Expression\Terminal\Match;
use ju1ius\Pegasus\Grammar\Optimization\CompositeReducerTrait;
use ju1ius\Pegasus\Grammar\Optimization\RegExpOptimization;
use ju1ius\Pegasus\Grammar\OptimizationContext;
use ju1ius\Pegasus\Utils\Iter;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
abstract class MatchJoiningOptimization extends RegExpOptimization
{
    use CompositeReducerTrait;

    /**
     * @inheritDoc
     */
    public function willPostProcessExpression(Expression $expr, OptimizationContext $context): bool
    {
        return Iter::someConsecutive(function ($child) {
            return $this->isEligibleChild($child);
        }, 2, $expr);
    }

    /**
     * @inheritDoc
     */
    public function postProcessExpression(Expression $expr, OptimizationContext $context): ?Expression
    {
        $newChildren = [];
        $matches = [];
        /** @var Composite $expr */
        foreach ($expr as $i => $child) {
            if ($this->isEligibleChild($child)) {
                $matches[] = $child;
            } else {
                if ($matches) {
                    array_push($newChildren, $this->joinMatches($matches));
                }
                $matches = [];
                $newChildren[] = $child;
            }
        }
        if ($matches) {
            array_push($newChildren, $this->joinMatches($matches));
        }

        return $this->finishReduction($expr, $newChildren);
    }

    /**
     * @todo handle non mergeable flags
     *
     * @param Expression $child
     *
     * @return bool
     */
    protected function isEligibleChild(Expression $child): bool
    {
        return $child instanceof Match || $child instanceof Literal;
    }

    /**
     * @param Match[] $matches
     *
     * @return Match
     */
    protected function joinMatches(array $matches)
    {
        $patterns = $this->createPatterns($matches);
        $pattern = $this->joinPatterns($patterns);

        return $this->createMatch($pattern);
    }

    /**
     * @param Match[] $matches
     *
     * @return string[]
     */
    protected function createPatterns(array $matches): array
    {
        return array_map(function ($match) {
            return $this->createPattern($match);
        }, $matches);
    }

    /**
     * @param string[] $patterns
     *
     * @return string|array
     */
    protected function joinPatterns(array $patterns)
    {
        return implode('', $patterns);
    }

    /**
     * @param Expression $expr
     *
     * @return string
     */
    protected function createPattern(Expression $expr): string
    {
        return $this->manipulator->patternFor($expr);
    }

    /**
     * @param string $pattern
     *
     * @return Match
     */
    protected function createMatch($pattern)
    {
        return new Match($pattern);
    }
}
