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
 * A predicate and an adjacent Match in a Choice can be joined into a single Match.
 *
 * @author ju1ius <ju1ius@laposte.net>
 */
class JoinPredicateOrNestedMatch extends Optimization
{
    use PredicateNestedMatchJoinerTrait, PredicateMatchChoiceJoinerTrait {
        PredicateNestedMatchJoinerTrait::reduce insteadof PredicateMatchChoiceJoinerTrait;
        PredicateNestedMatchJoinerTrait::isEligibleMatch insteadof PredicateMatchChoiceJoinerTrait;

        PredicateMatchChoiceJoinerTrait::doApply insteadof PredicateNestedMatchJoinerTrait;
        PredicateMatchChoiceJoinerTrait::isEligiblePredicate insteadof PredicateNestedMatchJoinerTrait;
        PredicateMatchChoiceJoinerTrait::isEligiblePair insteadof PredicateNestedMatchJoinerTrait;
        PredicateMatchChoiceJoinerTrait::someEligiblePairs insteadof PredicateNestedMatchJoinerTrait;
        PredicateMatchChoiceJoinerTrait::finishReduction insteadof PredicateNestedMatchJoinerTrait;
    }
}
