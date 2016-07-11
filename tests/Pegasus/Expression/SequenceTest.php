<?php

namespace ju1ius\Pegasus\Tests\Expression;

use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\Builder;
use ju1ius\Pegasus\Node;
use ju1ius\Pegasus\Node\Composite;
use ju1ius\Pegasus\Node\Terminal;
use ju1ius\Pegasus\Tests\ExpressionTestCase;

class SequenceTest extends ExpressionTestCase
{
    /**
     * @dataProvider getMatchProvider
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
    public function getMatchProvider()
    {
        return [
            [
                Builder::create()->rule('seq')->seq()
                    ->literal('foo')
                    ->literal('bar')
                    ->getGrammar(),
                ['foobar'],
                new Composite('seq', 0, 6, [
                    new Terminal('', 0, 3, 'foo'),
                    new Terminal('', 3, 6, 'bar'),
                ])
            ],
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
                Builder::create()->rule('seq')->seq()
                    ->literal('foo')
                    ->literal('bar')
                    ->getGrammar(),
                ['barbaz'],
            ]
        ];
    }

}
