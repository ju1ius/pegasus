<?php
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Tests\Optimization;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Match;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\Builder as GrammarBuilder;
use ju1ius\Pegasus\Grammar\ExpressionBuilder;
use ju1ius\Pegasus\Grammar\Optimization\CombineQuantifiedMatch;
use ju1ius\Pegasus\Grammar\OptimizationContext;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
class CombineQuantifiedMatchTest extends OptimizationTestCase
{

    /**
     * @dataProvider getTestAcceptsExpressionProvider
     *
     * @param Expression $expr
     * @param int        $contextType
     * @param bool       $expected
     */
    public function testAcceptsExpression(Expression $expr, $contextType, $expected)
    {
        $grammar = $this->createMock(Grammar::class);
        $ctx = OptimizationContext::of($grammar, $contextType);
        $optim = new CombineQuantifiedMatch();
        $result = $optim->willPostProcessExpression($expr, $ctx);
        $this->assertSame($expected, $result);
    }

    public function getTestAcceptsExpressionProvider()
    {
        return [
            'Returns false in capturing context.' => [
                ExpressionBuilder::create()->between(2, 4)->match('a')->getExpression(),
                OptimizationContext::TYPE_CAPTURING,
                false,
            ],
            'Applies to a quantified match in matching context.' => [
                ExpressionBuilder::create()->between(2, 4)->match('a')->getExpression(),
                OptimizationContext::TYPE_MATCHING,
                true,
            ],
            'Applies to a quantified regexp in matching context.' => [
                ExpressionBuilder::create()->between(2, 4)->regexp('a')->getExpression(),
                OptimizationContext::TYPE_MATCHING,
                true,
            ],
            'Returns false for quantified expression other than match or regexp.' => [
                ExpressionBuilder::create()->between(2, 4)->literal('a')->getExpression(),
                OptimizationContext::TYPE_MATCHING,
                false,
            ],
            'Returns false for anything other than quantifier.' => [
                ExpressionBuilder::create()->assert()->literal('a')->getExpression(),
                OptimizationContext::TYPE_MATCHING,
                false,
            ]
        ];
    }

    /**
     * @dataProvider getTestApplyProvider
     *
     * @param Grammar    $grammar
     * @param Expression $expected
     */
    public function testApply(Grammar $grammar, Expression $expected)
    {
        $ctx = OptimizationContext::of($grammar, OptimizationContext::TYPE_MATCHING);
        $result = $this->applyOptimization(new CombineQuantifiedMatch(), $grammar, $ctx);
        $this->assertExpressionEquals($expected, $result);
    }

    public function getTestApplyProvider()
    {
        return [
            'Quantified match with an upper bound' => [
                GrammarBuilder::create()->rule('test')
                    ->between(2, 4)->match('a')
                    ->getGrammar(),
                new Match('(?>a){2,4}', [], 'test')
            ],
            'Quantified match with no upper bound' => [
                GrammarBuilder::create()->rule('test')
                    ->atLeast(2)->match('a')
                    ->getGrammar(),
                new Match('(?>a){2,}', [], 'test')
            ],
            'Exact quantified match' => [
                GrammarBuilder::create()->rule('test')
                    ->exactly(2)->match('a')
                    ->getGrammar(),
                new Match('(?>a){2}', [], 'test')
            ],
            'zero-or-more quantified match' => [
                GrammarBuilder::create()->rule('test')
                    ->zeroOrMore()->match('a')
                    ->getGrammar(),
                new Match('(?>a)*', [], 'test')
            ],
            'one-or-more quantified match' => [
                GrammarBuilder::create()->rule('test')
                    ->oneOrMore()->match('a')
                    ->getGrammar(),
                new Match('(?>a)+', [], 'test')
            ],
            'optional match' => [
                GrammarBuilder::create()->rule('test')
                    ->optional()->match('a')
                    ->getGrammar(),
                new Match('(?>a)?', [], 'test')
            ],
        ];
    }
}
