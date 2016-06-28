<?php

namespace ju1ius\Test\Pegasus\Grammar;

use ju1ius\Test\Pegasus\PegasusTestCase;

use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\Builder;
use ju1ius\Pegasus\Expression\Sequence;
use ju1ius\Pegasus\Expression\OneOf;
use ju1ius\Pegasus\Expression\Quantifier;
use ju1ius\Pegasus\Expression\Literal;
use ju1ius\Pegasus\Expression\Regex;
use ju1ius\Pegasus\Expression\Reference as Ref;


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
            [
                [
                    'start' => new Sequence([
                        new Literal('foo'),
                        new Ref('bar')
                    ]),
                    'bar' => new OneOf([
                        new Literal('bar'),
                        new Literal('baz')
                    ])
                ],
                Builder::build()
                    ->rule('start')
                        ->seq()
                            ->literal('foo')
                            ->ref('bar')
                    ->rule('bar')
                        ->oneOf()
                            ->literal('bar')
                            ->literal('baz')
                    ->getGrammar()
            ],
            [
                [
                    'start' => new OneOf([
                        new Sequence([
                            new Literal('foo'),
                            new Literal('bar'),
                        ]),
                        new Sequence([
                            new Ref('beg'),
                            new Ref('food'),
                        ]),
                    ]),
                    'beg' => new Sequence([
                        new Literal('Can'),
                        new OneOf([
                            new Literal('I'),
                            new Literal('We'),
                        ]),
                        new Literal('haz')
                    ]),
                    'food' => new Sequence([
                        new OneOf([
                            new Literal('cheez'),
                            new Literal('ham'),
                        ]),
                        new Literal('burger')
                    ])
                ],
                Builder::build()
                    ->rule('start')->alt()
                        ->seq()
                            ->literal('foo')
                            ->literal('bar')
                        ->end()
                        ->seq()
                            ->ref('beg')
                            ->ref('food')
                    ->rule('beg')->seq()
                        ->literal('Can')
                        ->alt()
                            ->literal('I')
                            ->literal('We')
                        ->end()
                        ->literal('haz')
                    ->rule('food')->seq()
                        ->alt()
                            ->literal('cheez')
                            ->literal('ham')
                        ->end()
                        ->literal('burger')
                    ->getGrammar()
            ],
            [
                [
                    'start' => new Sequence([
                        new Quantifier([
                            new Literal('foo')
                        ], 1, INF),
                        new Quantifier([
                            new Literal('bar')
                        ], 1, INF),
                    ])
                ],
                Builder::build()
                    ->rule('start')->seq()
                        ->q(1)->literal('foo')
                        ->q(1)->literal('bar')
                    ->getGrammar()
            ]
        ];
    }
    
}
