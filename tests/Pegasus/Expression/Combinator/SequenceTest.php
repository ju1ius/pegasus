<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests\Expression\Combinator;

use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\CST\Node\Composite;
use ju1ius\Pegasus\CST\Node\Decorator;
use ju1ius\Pegasus\CST\Node\Terminal;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\GrammarBuilder;
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
                    ->ignore()->literal('bar')
                    ->literal('baz')
                    ->getGrammar(),
                ['foobarbaz'],
                new Composite('seq', 0, 9, [
                    new Terminal('', 0, 3, 'foo'),
                    new Terminal('', 6, 9, 'baz'),
                ])
            ],
            'With only one capturing expression, lifts the result if it is not a grammar rule.' => [
                GrammarBuilder::create()->rule('seq')->seq()
                    ->literal('foo')
                    ->ignore()->literal('bar')
                    ->getGrammar(),
                ['foobar'],
                new Terminal('seq', 0, 3, 'foo')
            ],
            'With only one capturing expression, decorates the result if it is a grammar rule.' => [
                GrammarBuilder::create()
                    ->rule('seq')->seq()
                        ->ref('foo')
                        ->ignore()->literal('bar')
                    ->rule('foo')->literal('foo')
                    ->getGrammar(),
                ['foobar'],
                new Decorator('seq', 0, 6, new Terminal('foo', 0, 3, 'foo'))
            ],
            'With no capturing expression, returns true' => [
                GrammarBuilder::create()->rule('seq')->seq()
                    ->ignore()->literal('foo')
                    ->ignore()->literal('bar')
                    ->getGrammar(),
                ['foobar'],
                true
            ],
            'Skips a reference to a non-capturing rule' => [
                GrammarBuilder::create()
                    ->rule('seq')->sequence()
                        ->literal('a')
                        ->ref('b')
                        ->literal('c')
                    ->rule('b')->ignore()->literal('b')
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
