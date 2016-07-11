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

use ju1ius\Pegasus\Debug\Debug;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Optimization\CombineQuantifiedMatch;
use ju1ius\Pegasus\Optimization\FlattenCapturingSequence;
use ju1ius\Pegasus\Optimization\FlattenChoice;
use ju1ius\Pegasus\Optimization\FlattenMatchingSequence;
use ju1ius\Pegasus\Optimization\FlattenSequence;
use ju1ius\Pegasus\Optimization\InlineNonRecursiveRules;
use ju1ius\Pegasus\Optimization\Match\JoinMatchSequence;
use ju1ius\Pegasus\Optimization\OptimizationContext;
use ju1ius\Pegasus\Optimization\OptimizationSequence;
use ju1ius\Pegasus\Optimization\RemoveMeaninglessDecorator;
use ju1ius\Pegasus\Optimization\SimplifyRedundantQuantifier;
use ju1ius\Pegasus\Optimization\SimplifyTerminalToken;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
class Optimizer
{
    /**
     * @var OptimizationSequence
     */
    private $optimization;

    public function __construct()
    {
        $this->optimization = (new InlineNonRecursiveRules())
            ->add(new SimplifyRedundantQuantifier())
            ->add(new RemoveMeaninglessDecorator())
            ->add(new SimplifyTerminalToken())
            ->add(new FlattenSequence())
            ->add(new FlattenChoice())
            ->add(new CombineQuantifiedMatch())
            //->add(new JoinPredicateMatch())
            //->add(new JoinPredicateOrMatch())
            ->add(new JoinMatchSequence())
            //->add(new JoinMatchChoice())
        ;
    }

    public function optimize(Grammar $grammar)
    {
        $ctx = OptimizationContext::create($grammar);
        $grammar = $grammar->map(function ($expr) use ($ctx) {
            return $this->optimization->apply($expr, $ctx, true);
        });

        $ctx = OptimizationContext::create($grammar);
        return $grammar->filter(function ($expr, $name) use ($ctx) {
            return $ctx->isRelevantRule($name);
        });
    }
}
