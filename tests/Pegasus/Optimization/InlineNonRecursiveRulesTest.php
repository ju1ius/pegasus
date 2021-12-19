<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests\Optimization;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Combinator\OneOf;
use ju1ius\Pegasus\Expression\Combinator\Sequence;
use ju1ius\Pegasus\Expression\Decorator\Ignore;
use ju1ius\Pegasus\Expression\Decorator\ZeroOrMore;
use ju1ius\Pegasus\Expression\Terminal\Literal;
use ju1ius\Pegasus\Expression\Terminal\RegExp;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\Optimization\Inlining\InlineMarkedNonRecursiveRules;
use ju1ius\Pegasus\Grammar\OptimizationContext;
use ju1ius\Pegasus\GrammarBuilder;
use ju1ius\Pegasus\Tests\PegasusAssert;
use PHPUnit\Framework\Assert;

class InlineNonRecursiveRulesTest extends OptimizationTestCase
{
    /**
     * @dataProvider provideTestApply
     */
    public function testApply(Grammar $grammar, string $rule, Expression $expected)
    {
        $result = $this->applyOptimization(
            new InlineMarkedNonRecursiveRules(),
            $grammar[$rule],
            OptimizationContext::of($grammar)
        );
        PegasusAssert::ExpressionEquals($expected, $result);
    }

    public function provideTestApply(): iterable
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
     */
    public function testAppliesTo(Grammar $grammar, string $rule, bool $expected)
    {
        $result = (new InlineMarkedNonRecursiveRules)
            ->willPreProcessExpression($grammar[$rule], OptimizationContext::of($grammar));
        Assert::assertSame($expected, $result);
    }

    public function provideTestAppliesTo(): iterable
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
     */
    public function testApplyOnWholeGrammar(Grammar $grammar, string $ruleToTest, Expression $expected)
    {
        $optimized = $this->optimizeGrammar($grammar, new InlineMarkedNonRecursiveRules());
        $actual = $optimized[$ruleToTest];
        PegasusAssert::ExpressionEquals($expected, $actual);
    }

    public function provideTestApplyOnWholeGrammar(): iterable
    {
        yield [
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
                    new RegExp('\s+'),
                    new RegExp('\#[^\n]*'),
                ]))),
                new Literal('foo'),
                new Ignore(new ZeroOrMore(new OneOf([
                    new RegExp('\s+'),
                    new RegExp('\#[^\n]*'),
                ])))
            ], 'test')
        ];
    }
}
