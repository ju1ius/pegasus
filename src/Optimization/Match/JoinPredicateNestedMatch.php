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
 * A predicate and an adjacent skipped match in a Sequence can be joined into a single skipped match.
 * @author ju1ius <ju1ius@laposte.net>
 */
class JoinPredicateNestedMatch extends Optimization
{
    use PredicateNestedMatchJoinerTrait, PredicateMatchSequenceJoinerTrait {
        PredicateNestedMatchJoinerTrait::reduce insteadof PredicateMatchSequenceJoinerTrait;
        PredicateNestedMatchJoinerTrait::isEligibleMatch insteadof PredicateMatchSequenceJoinerTrait;

        PredicateMatchSequenceJoinerTrait::doApply insteadof PredicateNestedMatchJoinerTrait;
        PredicateMatchSequenceJoinerTrait::isEligiblePredicate insteadof PredicateNestedMatchJoinerTrait;
        PredicateMatchSequenceJoinerTrait::isEligiblePair insteadof PredicateNestedMatchJoinerTrait;
        PredicateMatchSequenceJoinerTrait::someEligiblePairs insteadof PredicateNestedMatchJoinerTrait;
        PredicateMatchSequenceJoinerTrait::finishReduction insteadof PredicateNestedMatchJoinerTrait;
    }
}
