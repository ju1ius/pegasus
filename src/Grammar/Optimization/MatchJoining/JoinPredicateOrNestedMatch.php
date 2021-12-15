<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Grammar\Optimization\MatchJoining;

/**
 * A predicate and an adjacent Match in a Choice can be joined into a single Match.
 */
class JoinPredicateOrNestedMatch extends PredicateNestedMatchJoiningOptimization
{
    use PredicateMatchChoiceJoinerTrait;
}
