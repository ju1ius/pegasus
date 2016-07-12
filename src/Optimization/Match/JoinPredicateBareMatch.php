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

use ju1ius\Pegasus\Optimization\Optimization;

/**
 * A predicate and an adjacent match in a Sequence can be joined into a single match.
 *
 * @author ju1ius <ju1ius@laposte.net>
 */
class JoinPredicateBareMatch extends Optimization
{
    use PredicateBareMatchJoinerTrait, PredicateMatchSequenceJoinerTrait {
        PredicateMatchSequenceJoinerTrait::doApply insteadof PredicateBareMatchJoinerTrait;
        PredicateMatchSequenceJoinerTrait::reduce insteadof PredicateBareMatchJoinerTrait;
        PredicateMatchSequenceJoinerTrait::isEligibleMatch insteadof PredicateBareMatchJoinerTrait;
        PredicateMatchSequenceJoinerTrait::isEligiblePredicate insteadof PredicateBareMatchJoinerTrait;
        PredicateMatchSequenceJoinerTrait::isEligiblePair insteadof PredicateBareMatchJoinerTrait;
        PredicateMatchSequenceJoinerTrait::someEligiblePairs insteadof PredicateBareMatchJoinerTrait;
        PredicateMatchSequenceJoinerTrait::finishReduction insteadof PredicateBareMatchJoinerTrait;
    }
}
