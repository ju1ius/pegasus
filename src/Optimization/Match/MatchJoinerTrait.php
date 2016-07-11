<?php
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Optimization\Match;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Composite;
use ju1ius\Pegasus\Expression\Match;
use ju1ius\Pegasus\Optimization\CompositeReducerTrait;
use ju1ius\Pegasus\Optimization\OptimizationContext;
use ju1ius\Pegasus\Utils\Iter;

/**
 * @author ju1ius
 */
trait MatchJoinerTrait
{
    use CompositeReducerTrait;

    /**
     * @inheritDoc
     */
    protected function doAppliesTo(Expression $expr, OptimizationContext $context)
    {
        return Iter::someConsecutive(function ($child) {
            return $this->isEligibleChild($child);
        }, 2, $expr);
    }

    /**
     * @inheritDoc
     */
    protected function doApply(Expression $expr, OptimizationContext $context)
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
     */
    protected function createPatterns(array $matches)
    {
        return array_map(function ($match) {
            return $this->createPattern($match);
        }, $matches);
    }

    /**
     * @param string[] $patterns
     *
     * @return string
     */
    protected function joinPatterns(array $patterns)
    {
        return implode('', $patterns);
    }

    /**
     * @param Match $match
     *
     * @return string
     */
    protected function createPattern(Match $match)
    {
        if (count($match->flags)) {
            return sprintf('(?%s:%s)', implode('', $match->flags), $match->pattern);
        }

        return $match->pattern;
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
