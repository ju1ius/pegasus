<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Grammar\Optimization\MatchJoining;

/**
 * A predicate and an adjacent match in a Sequence can be joined into a single match.
 */
class JoinPredicateBareMatch extends PredicateBareMatchJoiningOptimization
{
    use PredicateMatchSequenceJoinerTrait;
}
