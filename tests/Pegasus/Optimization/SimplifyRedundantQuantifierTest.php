<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests\Optimization;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Decorator\OneOrMore;
use ju1ius\Pegasus\Expression\Decorator\Optional;
use ju1ius\Pegasus\Expression\Decorator\Quantifier;
use ju1ius\Pegasus\Expression\Decorator\ZeroOrMore;
use ju1ius\Pegasus\Expression\Terminal\Literal;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\Optimization\SimplifyRedundantQuantifier;
use ju1ius\Pegasus\Grammar\OptimizationContext;
use ju1ius\Pegasus\GrammarBuilder;
use ju1ius\Pegasus\Tests\PegasusAssert;
use PHPUnit\Framework\Assert;

class SimplifyRedundantQuantifierTest extends OptimizationTestCase
{
    /**
     * @dataProvider provideTestApply
     * @param Grammar    $grammar
     * @param Expression $expected
     */
    public function testApply(Grammar $grammar, Expression $expected)
    {
        $ctx = OptimizationContext::of($grammar, OptimizationContext::TYPE_MATCHING);
        $result = $this->applyOptimization(new SimplifyRedundantQuantifier(), $grammar, $ctx);
        PegasusAssert::ExpressionEquals($expected, $result);
        Assert::assertEquals((string)$expected, (string)$result);
    }

    public function provideTestApply()
    {
        yield '("foo"*)* => "foo"*' => [
            GrammarBuilder::create()->rule('test')->zeroOrMore()
                ->zeroOrMore()->literal('foo')
            ->getGrammar(),
            new ZeroOrMore(new Literal('foo'), 'test')
        ];
        yield '("foo"+)* => "foo"*' => [
            GrammarBuilder::create()->rule('test')->zeroOrMore()
                ->oneOrMore()->literal('foo')
                ->getGrammar(),
            new ZeroOrMore(new Literal('foo'), 'test')
        ];
        yield '("foo"?)* => "foo"*' => [
            GrammarBuilder::create()->rule('test')->zeroOrMore()
                ->optional()->literal('foo')
                ->getGrammar(),
            new ZeroOrMore(new Literal('foo'), 'test')
        ];
        yield '("foo"*)+ => "foo"*' => [
            GrammarBuilder::create()->rule('test')->oneOrMore()
                ->zeroOrMore()->literal('foo')
                ->getGrammar(),
            new ZeroOrMore(new Literal('foo'), 'test')
        ];
        yield '("foo"+)+ => "foo"+' => [
            GrammarBuilder::create()->rule('test')->oneOrMore()
                ->oneOrMore()->literal('foo')
                ->getGrammar(),
            new OneOrMore(new Literal('foo'), 'test')
        ];
        yield '("foo"?)+ => "foo"*' => [
            GrammarBuilder::create()->rule('test')->oneOrMore()
                ->optional()->literal('foo')
                ->getGrammar(),
            new ZeroOrMore(new Literal('foo'), 'test')
        ];
        yield '("foo"*)? => "foo"*' => [
            GrammarBuilder::create()->rule('test')->optional()
                ->zeroOrMore()->literal('foo')
                ->getGrammar(),
            new ZeroOrMore(new Literal('foo'), 'test')
        ];
        yield '("foo"+)? => "foo"*' => [
            GrammarBuilder::create()->rule('test')->optional()
                ->oneOrMore()->literal('foo')
                ->getGrammar(),
            new ZeroOrMore(new Literal('foo'), 'test')
        ];
        yield '("foo"?)? => "foo"?' => [
            GrammarBuilder::create()->rule('test')->optional()
                ->optional()->literal('foo')
                ->getGrammar(),
            new Optional(new Literal('foo'), 'test')
        ];
        // Now test that the optimization does not apply!
        yield '("foo"{2,2})?' => [
            GrammarBuilder::create()->rule('test')->optional()
                ->exactly(2)->literal('foo')
                ->getGrammar(),
            new Optional(new Quantifier(new Literal('foo'), 2, 2), 'test')
        ];
    }
}
