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

use ju1ius\Pegasus\Debug\Debug;
use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Literal;
use ju1ius\Pegasus\Expression\Match;
use ju1ius\Pegasus\Expression\OneOf;
use ju1ius\Pegasus\Expression\Sequence;
use ju1ius\Pegasus\Expression\Skip;
use ju1ius\Pegasus\Expression\ZeroOrMore;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\GrammarBuilder;
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
                GrammarBuilder::create()
                    ->rule('a')->ref('b')
                    ->rule('b')->literal('b')
                    ->getGrammar()
                    ->inline('b'),
                'a',
                new Literal('b', 'a')
            ],
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
            ->willPreProcessExpression($grammar[$rule], OptimizationContext::of($grammar));
        $this->assertSame($expected, $result);
    }

    public function getTestAppliesToProvider()
    {
        return [
            'Reference to a non-recursive rule' => [
                GrammarBuilder::create()
                    ->rule('a')->ref('b')
                    ->rule('b')->literal('b')
                    ->getGrammar()
                    ->inline('b'),
                'a',
                true
            ],
            'Reference to a non-recursive rule, not explicitly inlined' => [
                GrammarBuilder::create()
                    ->rule('a')->ref('b')
                    ->rule('b')->literal('b')
                    ->getGrammar(),
                'a',
                false
            ],
            'Not a reference' => [
                GrammarBuilder::create()
                    ->rule('a')->literal('b')
                    ->rule('b')->literal('b')
                    ->getGrammar()
                    ->inline('b'),
                'a',
                false
            ],
        ];
    }

    /**
     * @dataProvider getTestApplyOnWholeGrammarProvider
     *
     * @param Grammar $grammar
     * @param         $ruleToTest
     * @param         $expected
     */
    public function testApplyOnWholeGrammar(Grammar $grammar, $ruleToTest, $expected)
    {
        $optimized = $this->optimizeGrammar($grammar, new InlineNonRecursiveRules());
        $actual = $optimized[$ruleToTest];
        $this->assertExpressionEquals($expected, $actual);
    }

    public function getTestApplyOnWholeGrammarProvider()
    {
        return [
            [
                GrammarBuilder::create()
                    ->rule('test')->sequence()
                        ->ref('junk')
                        ->literal('foo')
                        ->ref('junk')
                    ->rule('junk')->skip()->zeroOrMore()->oneOf()
                        ->ref('whitespace')
                        ->ref('comment')
                    ->rule('whitespace')->match('\s+')
                    ->rule('comment')->match('\#[^\n]*')
                    ->getGrammar()
                    ->inline('comment', 'whitespace', 'junk'),
                'test',
                new Sequence([
                    new Skip(new ZeroOrMore(new OneOf([
                        new Match('\s+'),
                        new Match('\#[^\n]*'),
                    ]))),
                    new Literal('foo'),
                    new Skip(new ZeroOrMore(new OneOf([
                        new Match('\s+'),
                        new Match('\#[^\n]*'),
                    ])))
                ], 'test')
            ]
        ];
    }
}
