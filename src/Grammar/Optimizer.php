<?php declare(strict_types=1);

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
use ju1ius\Pegasus\Grammar\Optimization\PCREManipulator;
use ju1ius\Pegasus\Grammar\Optimization\RemoveMeaninglessDecorator;
use ju1ius\Pegasus\Grammar\Optimization\SimplifyRedundantQuantifier;
use ju1ius\Pegasus\Grammar\Optimization\SimplifyTerminalToken;
use SplObjectStorage;

final class Optimizer
{
    const LEVEL_0 = 0;
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

    /**
     * @var array<int, Optimization[]>
     */
    private static array $OPTIMIZATIONS = [
        1 => null,
        2 => null,
    ];

    private SplObjectStorage $passes;

    public function __construct()
    {
        $this->passes = new SplObjectStorage();
    }

    /**
     * Optimizes a grammar using the built-in optimization sets.
     * @throws Exception\MissingTraitAlias
     */
    public static function optimize(Grammar $grammar, OptimizationLevel $level, bool $deep = false): Grammar
    {
        if ($level === OptimizationLevel::NONE) {
            return $grammar;
        }
        $optimizer = new self();
        $optimizations = self::getOptimizations($level);
        $optimizer->addPasses(
            (new OptimizationPass(true))->add(...$optimizations)
        );

        return $optimizer->process($grammar, $deep);
    }

    /**
     * @return $this
     */
    public function addPasses(OptimizationPass ...$passes): self
    {
        foreach ($passes as $pass) {
            $this->passes->attach($pass);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function removePasses(OptimizationPass ...$passes): self
    {
        foreach ($passes as $pass) {
            $this->passes->detach($pass);
        }

        return $this;
    }

    /**
     * @param Grammar $grammar The grammar to process
     * @param bool $deep Whether to process the grammar's parents and traits.
     * @throws Exception\MissingTraitAlias
     */
    public function process(Grammar $grammar, bool $deep = false): Grammar
    {
        $grammar = clone $grammar;
        if ($deep) {
            $parent = $this->process($grammar->getParent(), $deep);
            $grammar->extends($parent);
            foreach ($grammar->getTraits() as $alias => $trait) {
                $trait = $this->process($trait, $deep);
                $grammar->use($trait, $alias);
            }
        }
        /** @var OptimizationPass $pass */
        foreach ($this->passes as $pass) {
            $grammar = $pass->process($grammar);
        }

        return $grammar;
    }

    /**
     * @return Optimization[]
     */
    private static function getOptimizations(OptimizationLevel $level): array
    {
        return self::$OPTIMIZATIONS[$level->value] ??= match ($level) {
            OptimizationLevel::NONE => [],
            OptimizationLevel::LEVEL_1 => [
                new FlattenMatchingSequence(),
                new FlattenCapturingSequence(),
                new FlattenChoice(),
            ],
            OptimizationLevel::LEVEL_2 => [
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
                new CombineQuantifiedMatch($manipulator = new PCREManipulator()),
                // join predicate matches,
                new JoinPredicateBareMatch($manipulator),
                new JoinPredicateNestedMatch($manipulator),
                // join predicate match choice,
                new JoinPredicateOrBareMatch($manipulator),
                new JoinPredicateOrNestedMatch($manipulator),
                // join match sequence,
                new JoinMatchMatchingSequence($manipulator),
                new JoinMatchCapturingSequence($manipulator),
                // join match choice
                new JoinMatchChoice($manipulator),
                //new RemoveUnusedRules(),
            ],
        };
    }
}
