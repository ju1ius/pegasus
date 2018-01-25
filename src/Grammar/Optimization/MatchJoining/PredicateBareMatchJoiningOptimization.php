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
use ju1ius\Pegasus\Expression\Terminal\Literal;
use ju1ius\Pegasus\Expression\Terminal\Match;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
abstract class PredicateBareMatchJoiningOptimization extends PredicateMatchJoiningOptimization
{
    /**
     * @param Expression $child
     *
     * @return string
     */
    abstract protected function prepareBarePattern(Expression $child): string;

    /**
     * @param Expression $child
     *
     * @return string
     */
    protected function preparePattern(Expression $child): string
    {
        return $this->prepareBarePattern($child);
    }

    /**
     * @param Expression $expr
     *
     * @return bool
     */
    protected function isEligibleMatch(Expression $expr): bool
    {
        return $expr instanceof Match || $expr instanceof Literal;
    }
}
