<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Grammar\Optimization\MatchJoining;

/**
 * A predicate and an adjacent ignored match in a Sequence can be joined into a single ignored match.
 */
final class JoinPredicateNestedMatch extends PredicateNestedMatchJoiningOptimization
{
    use PredicateMatchSequenceJoinerTrait;
}
