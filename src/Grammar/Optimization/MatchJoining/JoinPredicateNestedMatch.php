<?php declare(strict_types=1);
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Grammar\Optimization\MatchJoining;

/**
 * A predicate and an adjacent ignored match in a Sequence can be joined into a single ignored match.
 *
 * @author ju1ius <ju1ius@laposte.net>
 */
final class JoinPredicateNestedMatch extends PredicateNestedMatchJoiningOptimization
{
    use PredicateMatchSequenceJoinerTrait;
}
