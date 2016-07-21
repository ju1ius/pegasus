<?php

namespace ju1ius\Pegasus\Tests\Expression;

use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\GrammarBuilder;
use ju1ius\Pegasus\Node;
use ju1ius\Pegasus\Node\Composite;
use ju1ius\Pegasus\Node\Decorator;
use ju1ius\Pegasus\Node\Terminal;
use ju1ius\Pegasus\Tests\ExpressionTestCase;

class SequenceTest extends ExpressionTestCase
{
    /**
     * @dataProvider getMatchProvider
     *
     * @param Grammar   $expr
     * @param array     $match_args
     * @param Node|true $expected
     */
    public function testMatch(Grammar $expr, array $match_args, $expected)
    {
        if ($expected === true) {
            $this->assertTrue($this->parse($expr, ...$match_args));
        } else {
            $this->assertNodeEquals(
                $expected,
                $this->parse($expr, ...$match_args)
            );
        }
    }
    public function getMatchProvider()
    {
        return [
            'With only capturing expressions' => [
                GrammarBuilder::create()->rule('seq')->seq()
                    ->literal('foo')
                    ->literal('bar')
                    ->getGrammar(),
                ['foobar'],
                new Composite('seq', 0, 6, [
                    new Terminal('', 0, 3, 'foo'),
                    new Terminal('', 3, 6, 'bar'),
                ])
            ],
            'With non-capturing expressions' => [
                GrammarBuilder::create()->rule('seq')->seq()
                    ->literal('foo')
                    ->skip()->literal('bar')
                    ->literal('baz')
                    ->getGrammar(),
                ['foobarbaz'],
                new Composite('seq', 0, 9, [
                    new Terminal('', 0, 3, 'foo'),
                    new Terminal('', 6, 9, 'baz'),
                ])
            ],
            'With only one capturing expression' => [
                GrammarBuilder::create()->rule('seq')->seq()
                    ->literal('foo')
                    ->skip()->literal('bar')
                    ->getGrammar(),
                ['foobar'],
                new Decorator('seq', 0, 6, new Terminal('', 0, 3, 'foo'))
            ],
            'With no capturing expression' => [
                GrammarBuilder::create()->rule('seq')->seq()
                    ->skip()->literal('foo')
                    ->skip()->literal('bar')
                    ->getGrammar(),
                ['foobar'],
                true
            ],
            'With a reference to a non-capturing rule' => [
                GrammarBuilder::create()
                    ->rule('seq')->sequence()
                        ->literal('a')
                        ->ref('b')
                        ->literal('c')
                    ->rule('b')->skip()->literal('b')
                    ->getGrammar(),
                ['abc'],
                new Composite('seq', 0, 3, [
                    new Terminal('', 0, 1, 'a'),
                    new Terminal('', 2, 3, 'c'),
                ])
            ]
        ];
    }

    /**
     * @dataProvider getMatchErrorProvider
     * @expectedException \ju1ius\Pegasus\Parser\Exception\ParseError
     *
     * @param Grammar $expr
     * @param array   $match_args
     */
    public function testMatchError(Grammar $expr, array $match_args)
    {
        $this->parse($expr, ...$match_args);
    }
    public function getMatchErrorProvider()
    {
        return [
            [
                GrammarBuilder::create()->rule('seq')->seq()
                    ->literal('foo')
                    ->literal('bar')
                    ->getGrammar(),
                ['barbaz'],
            ]
        ];
    }

}
