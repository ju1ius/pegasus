<?php
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
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\Optimization;
use ju1ius\Pegasus\Grammar\Optimization\CompositeReducerTrait;
use ju1ius\Pegasus\Grammar\OptimizationContext;
use ju1ius\Pegasus\Utils\Iter;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
abstract class MatchJoiningOptimization extends Optimization
{
    use CompositeReducerTrait;

    /**
     * @inheritDoc
     */
    public function willPostProcessExpression(Expression $expr, OptimizationContext $context)
    {
        return Iter::someConsecutive(function ($child) {
            return $this->isEligibleChild($child);
        }, 2, $expr);
    }

    /**
     * @inheritDoc
     */
    public function postProcessExpression(Expression $expr, OptimizationContext $context)
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
    protected function isEligibleChild(Expression $child)
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
     * @return array
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
     * @param Expression $expr
     *
     * @return string
     */
    protected function createPattern(Expression $expr)
    {
        if ($expr instanceof Match) {
            if (count($expr->getFlags())) {
                return sprintf('(?%s:%s)', implode('', $expr->getFlags()), $expr->getPattern());
            }

            return $expr->getPattern();
        }
        if ($expr instanceof Literal) {
            return preg_quote($expr->getLiteral(), '/');
        }
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
