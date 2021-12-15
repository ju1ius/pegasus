<?php declare(strict_types=1);
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
use ju1ius\Pegasus\Expression\Combinator\OneOf;
use ju1ius\Pegasus\Expression\Combinator\Sequence;
use ju1ius\Pegasus\Expression\Decorator\Ignore;
use ju1ius\Pegasus\Expression\Decorator\ZeroOrMore;
use ju1ius\Pegasus\Expression\Terminal\Literal;
use ju1ius\Pegasus\Expression\Terminal\Match;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\Optimization\InlineNonRecursiveRules;
use ju1ius\Pegasus\Grammar\OptimizationContext;
use ju1ius\Pegasus\GrammarBuilder;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
class InlineNonRecursiveRulesTest extends OptimizationTestCase
{
    /**
     * @dataProvider provideTestApply
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

    public function provideTestApply()
    {
        yield [
            GrammarBuilder::create()
                ->rule('a')->ref('b')
                ->rule('b')->literal('b')
                ->getGrammar()
                ->inline('b'),
            'a',
            new Literal('b', 'a')
        ];
    }

    /**
     * @dataProvider provideTestAppliesTo
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

    public function provideTestAppliesTo()
    {
        yield 'Reference to a non-recursive rule' => [
            GrammarBuilder::create()
                ->rule('a')->ref('b')
                ->rule('b')->literal('b')
                ->getGrammar()
                ->inline('b'),
            'a',
            true
        ];
        yield 'Reference to a non-recursive rule, not explicitly inlined' => [
            GrammarBuilder::create()
                ->rule('a')->ref('b')
                ->rule('b')->literal('b')
                ->getGrammar(),
            'a',
            false
        ];
        yield 'Not a reference' => [
            GrammarBuilder::create()
                ->rule('a')->literal('b')
                ->rule('b')->literal('b')
                ->getGrammar()
                ->inline('b'),
            'a',
            false
        ];
    }

    /**
     * @dataProvider provideTestApplyOnWholeGrammar
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

    public function provideTestApplyOnWholeGrammar()
    {
        return [
            [
                GrammarBuilder::create()
                    ->rule('test')->sequence()
                        ->ref('junk')
                        ->literal('foo')
                        ->ref('junk')
                    ->rule('junk')->ignore()->zeroOrMore()->oneOf()
                        ->ref('whitespace')
                        ->ref('comment')
                    ->rule('whitespace')->match('\s+')
                    ->rule('comment')->match('\#[^\n]*')
                    ->getGrammar()
                    ->inline('comment', 'whitespace', 'junk'),
                'test',
                new Sequence([
                    new Ignore(new ZeroOrMore(new OneOf([
                        new Match('\s+'),
                        new Match('\#[^\n]*'),
                    ]))),
                    new Literal('foo'),
                    new Ignore(new ZeroOrMore(new OneOf([
                        new Match('\s+'),
                        new Match('\#[^\n]*'),
                    ])))
                ], 'test')
            ]
        ];
    }
}
