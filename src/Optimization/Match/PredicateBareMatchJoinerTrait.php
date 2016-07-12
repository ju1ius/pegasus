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
use ju1ius\Pegasus\Expression\Match;

/**
 * @author ju1ius
 */
trait PredicateBareMatchJoinerTrait
{
    use PredicateMatchJoinerTrait;

    /**
     * @param Expression $child
     *
     * @return string
     */
    abstract protected function prepareBarePattern(Expression $child);

    /**
     * @param Expression $child
     *
     * @return string
     */
    protected function preparePattern(Expression $child)
    {
        return $this->prepareBarePattern($child);
    }

    /**
     * @param Expression $expr
     *
     * @return bool
     */
    protected function isEligibleMatch(Expression $expr)
    {
        return $expr instanceof Match;
    }
}
