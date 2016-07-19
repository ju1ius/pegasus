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
use ju1ius\Pegasus\Expression\Literal;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\Builder;
use ju1ius\Pegasus\Grammar\Optimization\InlineNonRecursiveRules;
use ju1ius\Pegasus\Grammar\OptimizationContext;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
class InlineNonRecursiveRulesTest extends OptimizationTestCase
{
    /**
     * @dataProvider getTestApplyProvider
     * @param Grammar    $grammar
     * @param            $rule
     * @param Expression $expected
     */
    public function testApply(Grammar $grammar, $rule, Expression $expected)
    {
        $result = $this->applyOptimization(
            new InlineNonRecursiveRules(),
            $grammar[$rule],
            OptimizationContext::of($grammar)
        );
        $this->assertExpressionEquals($expected, $result);
    }

    public function getTestApplyProvider()
    {
        return [
            [
                Builder::create()
                    ->rule('a')->ref('b')
                    ->rule('b')->literal('b')
                    ->getGrammar()
                    ->inline('b'),
                'a',
                new Literal('b', 'a')
            ]
        ];
    }

    /**
     * @dataProvider getTestAppliesToProvider
     *
     * @param Grammar $grammar
     * @param         $rule
     * @param         $expected
     */
    public function testAppliesTo(Grammar $grammar, $rule, $expected)
    {
        $result = (new InlineNonRecursiveRules)
            ->appliesTo($grammar[$rule], OptimizationContext::of($grammar));
        $this->assertSame($expected, $result);
    }

    public function getTestAppliesToProvider()
    {
        return [
            'Reference to a non-recursive rule' => [
                Builder::create()
                    ->rule('a')->ref('b')
                    ->rule('b')->literal('b')
                    ->getGrammar()
                    ->inline('b'),
                'a',
                true
            ],
            'Reference to a non-recursive rule, not explicitly inlined' => [
                Builder::create()
                    ->rule('a')->ref('b')
                    ->rule('b')->literal('b')
                    ->getGrammar(),
                'a',
                false
            ],
            'Not a reference' => [
                Builder::create()
                    ->rule('a')->literal('b')
                    ->rule('b')->literal('b')
                    ->getGrammar()
                    ->inline('b'),
                'a',
                false
            ],
        ];
    }
}
