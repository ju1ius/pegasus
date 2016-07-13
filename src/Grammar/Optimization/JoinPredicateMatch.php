<?php
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Grammar\Optimization;

use ju1ius\Pegasus\Grammar\Optimization\MatchJoining\JoinPredicateBareMatch;
use ju1ius\Pegasus\Grammar\Optimization\MatchJoining\JoinPredicateNestedMatch;
use ju1ius\Pegasus\Grammar\OptimizationSequence;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
final class JoinPredicateMatch extends OptimizationSequence
{
    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct(new JoinPredicateBareMatch(), new JoinPredicateNestedMatch());
    }
}
