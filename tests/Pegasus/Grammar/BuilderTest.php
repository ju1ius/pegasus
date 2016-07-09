<?php

namespace ju1ius\Pegasus\Tests\Grammar;

use ju1ius\Pegasus\Expression\Literal;
use ju1ius\Pegasus\Expression\Not;
use ju1ius\Pegasus\Expression\OneOf;
use ju1ius\Pegasus\Expression\Quantifier;
use ju1ius\Pegasus\Expression\Reference as Ref;
use ju1ius\Pegasus\Expression\Sequence as Seq;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\Builder;
use ju1ius\Pegasus\Tests\PegasusTestCase;

class BuilderTest extends PegasusTestCase
{
    /**
     * @dataProvider testBasicRulesProvider
     */
    public function testBasicRules($input, $expected)
    {
        $actual = Grammar::fromArray($input);
        $this->assertGrammarEquals($expected, $actual);
    }
    public function testBasicRulesProvider()
    {
        return [
            'Sequence' => [
                [
                    'start' => new Seq([new Literal('foo'), new Ref('bar')])
                ],
                Builder::create()->rule('start')->seq()
                    ->literal('foo')
                    ->ref('bar')
                    ->getGrammar()
            ],
            'Choice' => [
                [
                    'start' => new OneOf([new Literal('bar'), new Literal('baz')])
                ],
                Builder::create()->rule('start')->oneOf()
                    ->literal('bar')
                    ->literal('baz')
                    ->getGrammar()
            ],
            'Choice of sequences' => [
                [
                    'start' => new OneOf([
                        new Seq([new Literal('foo'), new Literal('bar')]),
                        new Seq([new Ref('baz'), new Ref('qux')]),
                    ])
                ],
                Builder::create()->rule('start')->oneOf()
                    ->seq()->literal('foo')->literal('bar')->end()
                    ->seq()->ref('baz')->ref('qux')->end()
                    ->getGrammar()
            ],
            'Squence of choices' => [
                [
                    'start' => new Seq([
                        new OneOf([new Literal('foo'), new Literal('bar')]),
                        new OneOf([new Ref('baz'), new Ref('qux')]),
                    ])
                ],
                Builder::create()->rule('start')->seq()
                    ->oneOf()->literal('foo')->literal('bar')->end()
                    ->oneOf()->ref('baz')->ref('qux')->end()
                    ->getGrammar()
            ],
            'Top-level decorator' => [
                [
                    'start' => new Quantifier(new Literal('foo'), 1, INF)
                ],
                Builder::create()->rule('start')->q(1)->literal('foo')->getGrammar()
            ],
            'Nested decorators' => [
                [
                    'start' => new Not(new Quantifier(new Literal('foo'), 1, 1))
                ],
                Builder::create()->rule('start')->not()->exactly(1)->literal('foo')->getGrammar()
            ],
            'Quantifiers' => [
                [
                    'start' => new Seq([
                        new Quantifier(new Literal('foo'), 1, INF),
                        new Quantifier(new Literal('bar'), 1, 1),
                        new Quantifier(new Literal('baz'), 2, 42),
                    ])
                ],
                Builder::create()
                    ->rule('start')->seq()
                        ->q(1)->literal('foo')
                        ->exactly(1)->literal('bar')
                        ->q(2, 42)->literal('baz')
                    ->getGrammar()
            ]
        ];
    }
}
