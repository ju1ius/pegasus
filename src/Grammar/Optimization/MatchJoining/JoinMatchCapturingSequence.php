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
use ju1ius\Pegasus\Expression\Terminal\GroupMatch;
use ju1ius\Pegasus\Expression\Terminal\Literal;
use ju1ius\Pegasus\Expression\Terminal\Match;
use ju1ius\Pegasus\Expression\Combinator\Sequence;
use ju1ius\Pegasus\Expression\Decorator\Skip;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\Optimization;
use ju1ius\Pegasus\Grammar\OptimizationContext;
use ju1ius\Pegasus\Utils\Iter;

/**
 * @todo handle literals
 *
 * @author ju1ius <ju1ius@laposte.net>
 */
final class JoinMatchCapturingSequence extends MatchJoiningOptimization
{
    /**
     * @inheritDoc
     */
    public function willPostProcessExpression(Expression $expr, OptimizationContext $context): bool
    {
        return $context->isCapturing()
            && $expr instanceof Sequence
            && !$this->disqualifyingCaptures($expr)
            && Iter::someConsecutive(function ($child) {
                return $this->isEligibleChild($child);
            }, 2, $expr);
    }

    /**
     * @inheritDoc
     */
    protected function isEligibleChild(Expression $child)
    {
        return parent::isEligibleChild($child)
            || ($child instanceof GroupMatch && $child->getCaptureCount() === 1)
            || ($child instanceof Skip && parent::isEligibleChild($child[0]));
    }

    /**
     * @inheritDoc
     */
    protected function createPatterns(array $matches)
    {
        $groupCount = 0;
        $patterns = array_map(function ($expr) use(&$groupCount) {
            if ($expr instanceof Match) {
                $groupCount++;
                if (count($expr->getFlags())) {
                    return sprintf('((?%s)%s)', implode('', $expr->getFlags()), $expr->getPattern());
                }
                return sprintf('(%s)', $expr->getPattern());
            }
            if ($expr instanceof Literal) {
                $groupCount++;

                return sprintf('(%s)', preg_quote($expr->getLiteral(), '/'));
            }
            if ($expr instanceof GroupMatch) {
                $groupCount += $expr->getCaptureCount();
                if (count($expr->getFlags())) {
                    return sprintf('(?>(?%s)%s)', implode('', $expr->getFlags()), $expr->getPattern());
                }
                return sprintf('(?>%s)', $expr->getPattern());
            }
            if ($expr instanceof Skip) {
                if ($expr[0] instanceof Literal) {
                    return sprintf('(?>%s)', preg_quote($expr[0]->getLiteral(), '/'));
                }
                if ($expr[0] instanceof Match) {
                    if (count($expr[0]->getFlags())) {
                        return sprintf('(?>(?%s)%s)', implode('', $expr[0]->getFlags()), $expr[0]->getPattern());
                    }
                    return sprintf('(?>%s)', $expr[0]->getPattern());
                }
            }
        }, $matches);

        return ['patterns' => $patterns, 'group_count' => $groupCount];
    }

    /**
     * @inheritDoc
     */
    protected function joinPatterns(array $matchInfo)
    {
        $matchInfo['pattern'] = implode('', $matchInfo['patterns']);

        return $matchInfo;
    }

    /**
     * @inheritDoc
     */
    protected function createMatch($matchInfo)
    {
        $match = new Match($matchInfo['pattern']);
        if (!$matchInfo['group_count']) {
            return new Skip($match);
        }

        return new GroupMatch($match, $matchInfo['group_count']);
    }

    private function disqualifyingCaptures(Composite $expr)
    {
        return (
            $expr->some(function (Expression $child) {
                return $child->isCapturing() && $this->isEligibleChild($child);
            })
            && $expr->some(function (Expression $child) {
                return $this->isCaptureIncompatible($child);
            })
        );
    }

    private function isCaptureIncompatible(Expression $child)
    {
        return $child->isCapturing() && !$this->isEligibleChild($child);
    }
}
