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

use ju1ius\Pegasus\Optimization\OptimizationSequence;

/**
 * A predicate and an adjacent Match in a Choice can be joined into a single Match.
 *
 * @author ju1ius <ju1ius@laposte.net>
 */
class JoinPredicateOrMatch extends OptimizationSequence
{
    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct(new JoinPredicateOrBareMatch(), new JoinPredicateOrNestedMatch());
    }
}