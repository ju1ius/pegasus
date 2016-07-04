<?php

namespace ju1ius\Pegasus\Tests\Expression;

use ju1ius\Pegasus\Expression\Literal;
use ju1ius\Pegasus\Expression\Sequence;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\Builder;
use ju1ius\Pegasus\Node;
use ju1ius\Pegasus\Node\Literal as Lit;
use ju1ius\Pegasus\Node\Sequence as Seq;
use ju1ius\Pegasus\Tests\ExpressionTestCase;

class SequenceTest extends ExpressionTestCase
{
    /**
     * @dataProvider testMatchProvider
     *
     * @param Grammar $expr
     * @param array   $match_args
     * @param Node    $expected
     */
    public function testMatch(Grammar $expr, array $match_args, Node $expected)
    {
        $this->assertNodeEquals(
            $expected,
            $this->parse($expr, ...$match_args)
        );
    }
    public function testMatchProvider()
    {
        return [
            [
                Builder::create()->rule('seq')->seq()
                    ->literal('foo')
                    ->literal('bar')
                    ->getGrammar(),
                ['foobar'],
                new Seq('seq', 'foobar', 0, 6, [
                    new Lit('', 'foobar', 0, 3),
                    new Lit('', 'foobar', 3, 6),
                ])
            ],
        ];
    }

    /**
     * @dataProvider testMatchErrorProvider
     * @expectedException \ju1ius\Pegasus\Exception\ParseError
     *
     * @param Grammar $expr
     * @param array   $match_args
     */
    public function testMatchError(Grammar $expr, array $match_args)
    {
        $this->parse($expr, ...$match_args);
    }
    public function testMatchErrorProvider()
    {
        return [
            [
                Builder::create()->rule('seq')->seq()
                    ->literal('foo')
                    ->literal('bar')
                    ->getGrammar(),
                ['barbaz'],
            ]
        ];
    }

}
