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
class JoinPredicateOrBareMatch extends Optimization
{
    use PredicateBareMatchJoinerTrait, PredicateMatchChoiceJoinerTrait {
        PredicateMatchChoiceJoinerTrait::prepareBarePattern insteadof PredicateBareMatchJoinerTrait;
        PredicateMatchChoiceJoinerTrait::doApply insteadof PredicateBareMatchJoinerTrait;
        PredicateMatchChoiceJoinerTrait::reduce insteadof PredicateBareMatchJoinerTrait;
        PredicateMatchChoiceJoinerTrait::isEligibleMatch insteadof PredicateBareMatchJoinerTrait;
        PredicateMatchChoiceJoinerTrait::isEligiblePredicate insteadof PredicateBareMatchJoinerTrait;
        PredicateMatchChoiceJoinerTrait::isEligiblePair insteadof PredicateBareMatchJoinerTrait;
        PredicateMatchChoiceJoinerTrait::someEligiblePairs insteadof PredicateBareMatchJoinerTrait;
        PredicateMatchChoiceJoinerTrait::finishReduction insteadof PredicateBareMatchJoinerTrait;
    }
}
