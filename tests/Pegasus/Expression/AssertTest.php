<?php

namespace ju1ius\Pegasus\Tests\Expression;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\Builder;
use ju1ius\Pegasus\Node;
use ju1ius\Pegasus\Tests\ExpressionTestCase;

class AssertTest extends ExpressionTestCase
{
    /**
     * @dataProvider testMatchProvider
     *
     * @param Grammar $expr
     * @param array   $args
     * @param Node    $expected
     */
    public function testMatch(Grammar $expr, array $args, Node $expected)
    {
        $this->assertNodeEquals(
            $expected,
            $this->parse($expr, ...$args)
        );
    }

    public function testMatchProvider()
    {
        return [
            [
                Builder::create()->rule('assert')
                    ->assert()->literal('foobar')
                    ->getGrammar(),
                ['foobar'],
                new Node('assert', 0, 0),
            ],
            [
                Builder::create()->rule('assert')
                    ->assert()->literal('bar')
                    ->getGrammar(),
                ['foobar', 3],
                new Node('assert', 3, 3),
            ],
        ];
    }

    /**
     * @dataProvider testMatchErrorProvider
     * @expectedException \ju1ius\Pegasus\Exception\ParseError
     *
     * @param Grammar $expr
     * @param array   $args
     */
    public function testMatchError(Grammar $expr, array $args)
    {
        $this->parse($expr, ...$args);
    }

    public function testMatchErrorProvider()
    {
        return [
            [
                Builder::create()->rule('assert')
                    ->assert()->literal('foo')
                    ->getGrammar(),
                ['barbaz'],
            ],
        ];
    }

}
