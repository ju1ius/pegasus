<?php declare(strict_types=1);
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Grammar;

use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\Exception\UnknownOptimizationLevel;
use ju1ius\Pegasus\Grammar\Optimization\CombineQuantifiedMatch;
use ju1ius\Pegasus\Grammar\Optimization\Flattening\FlattenCapturingSequence;
use ju1ius\Pegasus\Grammar\Optimization\Flattening\FlattenChoice;
use ju1ius\Pegasus\Grammar\Optimization\Flattening\FlattenMatchingSequence;
use ju1ius\Pegasus\Grammar\Optimization\InlineNonRecursiveRules;
use ju1ius\Pegasus\Grammar\Optimization\MatchJoining\JoinMatchCapturingSequence;
use ju1ius\Pegasus\Grammar\Optimization\MatchJoining\JoinMatchChoice;
use ju1ius\Pegasus\Grammar\Optimization\MatchJoining\JoinMatchMatchingSequence;
use ju1ius\Pegasus\Grammar\Optimization\MatchJoining\JoinPredicateBareMatch;
use ju1ius\Pegasus\Grammar\Optimization\MatchJoining\JoinPredicateNestedMatch;
use ju1ius\Pegasus\Grammar\Optimization\MatchJoining\JoinPredicateOrBareMatch;
use ju1ius\Pegasus\Grammar\Optimization\MatchJoining\JoinPredicateOrNestedMatch;
use ju1ius\Pegasus\Grammar\Optimization\RemoveMeaninglessDecorator;
use ju1ius\Pegasus\Grammar\Optimization\RemoveUnusedRules;
use ju1ius\Pegasus\Grammar\Optimization\SimplifyRedundantQuantifier;
use ju1ius\Pegasus\Grammar\Optimization\SimplifyTerminalToken;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
final class Optimizer
{
    /**
     * Optimization level 1.
     *
     * Enables only transparent optimizations,
     * i.e. parsing a grammar and echoing it right away should yield no visual differences.
     */
    const LEVEL_1 = 1;

    /**
     * Optimization level 2.
     */
    const LEVEL_2 = 2;

    private const LEVELS = [
        self::LEVEL_1,
        self::LEVEL_2,
    ];

    private static $OPTIMIZATIONS = [
        self::LEVEL_1 => null,
        self::LEVEL_2 => null,
    ];

    /**
     * @var \SplObjectStorage
     */
    private $passes;

    public function __construct()
    {
        $this->passes = new \SplObjectStorage();
    }

    /**
     * @return int[]
     */
    public static function getLevels()
    {
        return self::LEVELS;
    }

    /**
     * Optimizes a grammar using the built-in optimization sets.
     *
     * @param Grammar $grammar
     * @param int     $level
     *
     * @return Grammar
     */
    public static function optimize(Grammar $grammar, $level = self::LEVEL_1)
    {
        $optimizer = new self();
        $optimizations = self::getOptimizations($level);
        $optimizer->addPasses(
            (new OptimizationPass(true))->add(...$optimizations)
        );

        return $optimizer->process($grammar);
    }

    /**
     * @param OptimizationPass[] ...$passes
     *
     * @return $this
     */
    public function addPasses(OptimizationPass ...$passes)
    {
        foreach ($passes as $pass) {
            $this->passes->attach($pass);
        }

        return $this;
    }

    /**
     * @param OptimizationPass[] ...$passes
     *
     * @return $this
     */
    public function removePasses(OptimizationPass ...$passes)
    {
        foreach ($passes as $pass) {
            $this->passes->detach($pass);
        }

        return $this;
    }

    /**
     * @param Grammar $grammar
     *
     * @return Grammar
     */
    public function process(Grammar $grammar)
    {
        $grammar = clone $grammar;
        /** @var OptimizationPass $pass */
        foreach ($this->passes as $pass) {
            $grammar = $pass->process($grammar);
        }

        return $grammar;
    }

    /**
     * @param $level
     *
     * @return Optimization
     */
    private static function getOptimizations($level)
    {
        if (!array_key_exists($level, self::$OPTIMIZATIONS)) {
            throw new UnknownOptimizationLevel($level, self::getLevels());
        }
        if (self::$OPTIMIZATIONS[$level] === null) {
            switch ($level) {
                case self::LEVEL_1:
                    self::$OPTIMIZATIONS[$level] = [
                        new FlattenMatchingSequence(),
                        new FlattenCapturingSequence(),
                        new FlattenChoice(),
                    ];
                    break;
                case self::LEVEL_2:
                    self::$OPTIMIZATIONS[$level] = [
                        new InlineNonRecursiveRules(),
                        new SimplifyRedundantQuantifier(),
                        new RemoveMeaninglessDecorator(),
                        new SimplifyTerminalToken(),
                        // flatten sequences
                        new FlattenMatchingSequence(),
                        new FlattenCapturingSequence(),
                        //
                        new FlattenChoice(),
                        //
                        new CombineQuantifiedMatch(),
                        // join predicate matches,
                        new JoinPredicateBareMatch(),
                        new JoinPredicateNestedMatch(),
                        // join predicate match choice,
                        new JoinPredicateOrBareMatch(),
                        new JoinPredicateOrNestedMatch(),
                        // join match sequence,
                        new JoinMatchMatchingSequence(),
                        new JoinMatchCapturingSequence(),
                        // join match choice
                        new JoinMatchChoice(),
                        new RemoveUnusedRules(),
                    ];
                    break;
            }
        }

        return self::$OPTIMIZATIONS[$level];
    }
}
