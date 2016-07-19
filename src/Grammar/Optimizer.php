<?php
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
use ju1ius\Pegasus\Grammar\Optimization\FlattenChoice;
use ju1ius\Pegasus\Grammar\Optimization\FlattenSequence;
use ju1ius\Pegasus\Grammar\Optimization\InlineNonRecursiveRules;
use ju1ius\Pegasus\Grammar\Optimization\JoinMatchChoice;
use ju1ius\Pegasus\Grammar\Optimization\JoinMatchSequence;
use ju1ius\Pegasus\Grammar\Optimization\JoinPredicateMatch;
use ju1ius\Pegasus\Grammar\Optimization\JoinPredicateOrMatch;
use ju1ius\Pegasus\Grammar\Optimization\RemoveMeaninglessDecorator;
use ju1ius\Pegasus\Grammar\Optimization\SimplifyRedundantQuantifier;
use ju1ius\Pegasus\Grammar\Optimization\SimplifyTerminalToken;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
class Optimizer
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

    private static $LEVELS = [
        self::LEVEL_1,
        self::LEVEL_2,
    ];

    private static $OPTIMIZATIONS = [
        self::LEVEL_1 => null,
        self::LEVEL_2 => null,
    ];

    /**
     * @return int[]
     */
    public static function getLevels()
    {
        return self::$LEVELS;
    }

    /**
     * @param Grammar $grammar
     * @param int     $level
     *
     * @return Grammar
     */
    public static function optimize(Grammar $grammar, $level = self::LEVEL_1)
    {
        $optimization = self::getOptimization($level);

        $grammar = $grammar->map(function ($expr, $i, $grammar) use ($optimization) {
            return $optimization->apply($expr, OptimizationContext::of($grammar), true);
        });

        return $grammar;

        return $grammar->filter(function ($expr, $name, $grammar) {
            return OptimizationContext::of($grammar)->isRelevantRule($name);
        });
    }

    /**
     * @param $level
     *
     * @return Optimization
     */
    private static function getOptimization($level)
    {
        if (!array_key_exists($level, self::$OPTIMIZATIONS)) {
            throw new UnknownOptimizationLevel($level, self::getLevels());
        }
        if (self::$OPTIMIZATIONS[$level] === null) {
            switch ($level) {
                case self::LEVEL_1:
                    self::$OPTIMIZATIONS[$level] = (new FlattenSequence())
                        ->add(new FlattenChoice());
                    break;
                case self::LEVEL_2:
                    self::$OPTIMIZATIONS[$level] = (new InlineNonRecursiveRules())
                        ->add(new SimplifyRedundantQuantifier())
                        ->add(new RemoveMeaninglessDecorator())
                        ->add(new SimplifyTerminalToken())
                        ->add(new FlattenSequence())
                        ->add(new FlattenChoice())
                        ->add(new CombineQuantifiedMatch())
                        ->add(new JoinPredicateMatch())
                        ->add(new JoinPredicateOrMatch())
                        ->add(new JoinMatchSequence())
                        ->add(new JoinMatchChoice())
                    ;
                    break;
            }
        }

        return self::$OPTIMIZATIONS[$level];
    }
}
