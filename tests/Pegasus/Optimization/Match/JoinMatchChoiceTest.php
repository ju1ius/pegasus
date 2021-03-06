<?php declare(strict_types=1);
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Tests\Optimization\Match;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Terminal\Match;
use ju1ius\Pegasus\Expression\Combinator\OneOf;
use ju1ius\Pegasus\Expression\Application\Reference;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\GrammarBuilder;
use ju1ius\Pegasus\Grammar\Optimization\MatchJoining\JoinMatchChoice;
use ju1ius\Pegasus\Grammar\OptimizationContext;
use ju1ius\Pegasus\Tests\Optimization\OptimizationTestCase;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
class JoinMatchChoiceTest extends RegExpOptimizationTestCase
{
    /**
     * @dataProvider provideTestApply
     *
     * @param Grammar    $input
     * @param Expression $expected
     */
    public function testApply(Grammar $input, Expression $expected)
    {
        $optim = $this->createOptimization(JoinMatchChoice::class);
        $ctx = OptimizationContext::of($input);

        $result = $this->applyOptimization($optim, $input, $ctx);
        $this->assertExpressionEquals($expected, $result, 'In capturing context');

        $result = $this->applyOptimization($optim, $input, $ctx->matching());
        $this->assertExpressionEquals($expected, $result, 'In matching context');
    }

    public function provideTestApply()
    {
        yield 'Choice of matches only' => [
            GrammarBuilder::create()->rule('test')->oneOf()
                ->match('a')
                ->match('b')
                ->match('c')
            ->getGrammar(),
            new Match('a|b|c', [], 'test')
        ];
        yield 'Choice of matches before something else' => [
            GrammarBuilder::create()->rule('test')->oneOf()
                ->match('a')
                ->match('b')
                ->ref('c')
                ->getGrammar(),
            new OneOf([
                new Match('a|b'),
                new Reference('c'),
            ], 'test')
        ];
        yield 'Choice of matches after something else' => [
            GrammarBuilder::create()->rule('test')->oneOf()
                ->ref('a')
                ->match('b')
                ->match('c')
                ->getGrammar(),
            new OneOf([
                new Reference('a'),
                new Match('b|c'),
            ], 'test')
        ];
    }

    /**
     * @dataProvider provideTestAppliesTo
     *
     * @param Grammar $input
     * @param bool    $applies
     */
    public function testAppliesTo(Grammar $input, $applies)
    {
        $optim = $this->createOptimization(JoinMatchChoice::class);
        $expr = $input->getStartExpression();
        $ctx = OptimizationContext::of($input);

        $result = $optim->willPostProcessExpression($expr, $ctx);
        $this->assertSame($applies, $result, 'In capturing context');

        $result = $optim->willPostProcessExpression($expr, $ctx->matching());
        $this->assertSame($applies, $result, 'In matching context');
    }

    public function provideTestAppliesTo()
    {
        return [
            'Choice of matches only' => [
                GrammarBuilder::create()->rule('test')->oneOf()
                    ->match('a')
                    ->match('b')
                    ->match('c')
                    ->getGrammar(),
                true
            ],
            'Choice of matches before something else' => [
                GrammarBuilder::create()->rule('test')->oneOf()
                    ->match('a')
                    ->match('b')
                    ->ref('c')
                    ->getGrammar(),
                true
            ],
            'Choice of matches after something else' => [
                GrammarBuilder::create()->rule('test')->oneOf()
                    ->ref('a')
                    ->match('b')
                    ->match('c')
                    ->getGrammar(),
                true
            ],
            'Choice with only one match' => [
                GrammarBuilder::create()->rule('test')->oneOf()
                    ->ref('a')
                    ->match('b')
                    ->ref('c')
                    ->getGrammar(),
                false
            ],
            'Choice with non-consecutive matches' => [
                GrammarBuilder::create()->rule('test')->oneOf()
                    ->match('a')
                    ->ref('b')
                    ->match('c')
                    ->getGrammar(),
                false
            ],
            'Not a choice' => [
                GrammarBuilder::create()->rule('test')->sequence()
                    ->match('a')
                    ->match('b')
                    ->match('c')
                    ->getGrammar(),
                false
            ]
        ];
    }
}
