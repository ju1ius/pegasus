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
use ju1ius\Pegasus\Expression\OneOrMore;
use ju1ius\Pegasus\Expression\Optional;
use ju1ius\Pegasus\Expression\Quantifier;
use ju1ius\Pegasus\Expression\ZeroOrMore;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\Builder;
use ju1ius\Pegasus\Grammar\OptimizationContext;
use ju1ius\Pegasus\Grammar\Optimization\SimplifyRedundantQuantifier;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
class SimplifyRedundantQuantifierTest extends OptimizationTestCase
{
    /**
     * @dataProvider getApplyProvider
     * @param Grammar    $grammar
     * @param Expression $expected
     */
    public function testApply(Grammar $grammar, Expression $expected)
    {
        $ctx = OptimizationContext::of($grammar, OptimizationContext::TYPE_MATCHING);
        $result = $this->applyOptimization(new SimplifyRedundantQuantifier(), $grammar, $ctx);
        $this->assertExpressionEquals($expected, $result);
        $this->assertEquals((string)$expected, (string)$result);
    }

    public function getApplyProvider()
    {
        return [
            '("foo"*)* => "foo"*' => [
                Builder::create()->rule('test')->zeroOrMore()
                    ->zeroOrMore()->literal('foo')
                ->getGrammar(),
                new ZeroOrMore(new Literal('foo'), 'test')
            ],
            '("foo"+)* => "foo"*' => [
                Builder::create()->rule('test')->zeroOrMore()
                    ->oneOrMore()->literal('foo')
                    ->getGrammar(),
                new ZeroOrMore(new Literal('foo'), 'test')
            ],
            '("foo"?)* => "foo"*' => [
                Builder::create()->rule('test')->zeroOrMore()
                    ->optional()->literal('foo')
                    ->getGrammar(),
                new ZeroOrMore(new Literal('foo'), 'test')
            ],
            '("foo"*)+ => "foo"*' => [
                Builder::create()->rule('test')->oneOrMore()
                    ->zeroOrMore()->literal('foo')
                    ->getGrammar(),
                new ZeroOrMore(new Literal('foo'), 'test')
            ],
            '("foo"+)+ => "foo"+' => [
                Builder::create()->rule('test')->oneOrMore()
                    ->oneOrMore()->literal('foo')
                    ->getGrammar(),
                new OneOrMore(new Literal('foo'), 'test')
            ],
            '("foo"?)+ => "foo"*' => [
                Builder::create()->rule('test')->oneOrMore()
                    ->optional()->literal('foo')
                    ->getGrammar(),
                new ZeroOrMore(new Literal('foo'), 'test')
            ],
            '("foo"*)? => "foo"*' => [
                Builder::create()->rule('test')->optional()
                    ->zeroOrMore()->literal('foo')
                    ->getGrammar(),
                new ZeroOrMore(new Literal('foo'), 'test')
            ],
            '("foo"+)? => "foo"*' => [
                Builder::create()->rule('test')->optional()
                    ->oneOrMore()->literal('foo')
                    ->getGrammar(),
                new ZeroOrMore(new Literal('foo'), 'test')
            ],
            '("foo"?)? => "foo"?' => [
                Builder::create()->rule('test')->optional()
                    ->optional()->literal('foo')
                    ->getGrammar(),
                new Optional(new Literal('foo'), 'test')
            ],
            // Now test that the optimization does not apply!
            '("foo"{2,2})?' => [
                Builder::create()->rule('test')->optional()
                    ->exactly(2)->literal('foo')
                    ->getGrammar(),
                new Optional(new Quantifier(new Literal('foo'), 2, 2), 'test')
            ]
        ];
    }
}
