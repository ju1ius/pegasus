<?php
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
use ju1ius\Pegasus\Expression\Match;
use ju1ius\Pegasus\Expression\OneOf;
use ju1ius\Pegasus\Expression\Reference;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\Builder;
use ju1ius\Pegasus\Grammar\Optimization\JoinMatchChoice;
use ju1ius\Pegasus\Grammar\OptimizationContext;
use ju1ius\Pegasus\Tests\Optimization\OptimizationTestCase;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
class JoinMatchChoiceTest extends OptimizationTestCase
{
    /**
     * @dataProvider getApplyProvider
     *
     * @param Grammar    $input
     * @param Expression $expected
     */
    public function testApply(Grammar $input, Expression $expected)
    {
        $optim = new \ju1ius\Pegasus\Grammar\Optimization\JoinMatchChoice();
        $ctx = OptimizationContext::create($input);

        $result = $this->applyOptimization($optim, $input, $ctx);
        $this->assertExpressionEquals($expected, $result, 'In capturing context');

        $result = $this->applyOptimization($optim, $input, $ctx->matching());
        $this->assertExpressionEquals($expected, $result, 'In matching context');
    }

    public function getApplyProvider()
    {
        return [
            'Choice of matches only' => [
                Builder::create()->rule('test')->oneOf()
                    ->match('a')
                    ->match('b')
                    ->match('c')
                ->getGrammar(),
                new Match('a|b|c', [], 'test')
            ],
            'Choice of matches before something else' => [
                Builder::create()->rule('test')->oneOf()
                    ->match('a')
                    ->match('b')
                    ->ref('c')
                    ->getGrammar(),
                new OneOf([
                    new Match('a|b'),
                    new Reference('c'),
                ], 'test')
            ],
            'Choice of matches after something else' => [
                Builder::create()->rule('test')->oneOf()
                    ->ref('a')
                    ->match('b')
                    ->match('c')
                    ->getGrammar(),
                new OneOf([
                    new Reference('a'),
                    new Match('b|c'),
                ], 'test')
            ],
        ];
    }

    /**
     * @dataProvider getAppliesToProvider
     *
     * @param Grammar $input
     * @param bool    $applies
     */
    public function testAppliesTo(Grammar $input, $applies)
    {
        $optim = new \ju1ius\Pegasus\Grammar\Optimization\JoinMatchChoice();
        $expr = $input->getStartExpression();
        $ctx = OptimizationContext::create($input);

        $result = $optim->appliesTo($expr, $ctx);
        $this->assertSame($applies, $result, 'In capturing context');

        $result = $optim->appliesTo($expr, $ctx->matching());
        $this->assertSame($applies, $result, 'In matching context');
    }

    public function getAppliesToProvider()
    {
        return [
            'Choice of matches only' => [
                Builder::create()->rule('test')->oneOf()
                    ->match('a')
                    ->match('b')
                    ->match('c')
                    ->getGrammar(),
                true
            ],
            'Choice of matches before something else' => [
                Builder::create()->rule('test')->oneOf()
                    ->match('a')
                    ->match('b')
                    ->ref('c')
                    ->getGrammar(),
                true
            ],
            'Choice of matches after something else' => [
                Builder::create()->rule('test')->oneOf()
                    ->ref('a')
                    ->match('b')
                    ->match('c')
                    ->getGrammar(),
                true
            ],
            'Choice with only one match' => [
                Builder::create()->rule('test')->oneOf()
                    ->ref('a')
                    ->match('b')
                    ->ref('c')
                    ->getGrammar(),
                false
            ],
            'Choice with non-consecutive matches' => [
                Builder::create()->rule('test')->oneOf()
                    ->match('a')
                    ->ref('b')
                    ->match('c')
                    ->getGrammar(),
                false
            ],
            'Not a choice' => [
                Builder::create()->rule('test')->sequence()
                    ->match('a')
                    ->match('b')
                    ->match('c')
                    ->getGrammar(),
                false
            ]
        ];
    }
}
