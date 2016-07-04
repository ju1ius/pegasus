<?php

namespace ju1ius\Pegasus\Tests\Expression;

use ju1ius\Pegasus\Grammar\Builder;
use ju1ius\Pegasus\Node;
use ju1ius\Pegasus\Tests\ExpressionTestCase;

class QuantifierTest extends ExpressionTestCase
{
    /**
     * @dataProvider testMatchProvider
     */
    public function testMatch($expr, $match_args, $expected)
    {
        $this->assertNodeEquals(
            $expected,
            $this->parse($expr, ...$match_args)
        );
    }
    public function testMatchProvider()
    {
        return [
            // exact number of occurences
            'exactly one "x" with "x"' => [
                Builder::create()->rule('one')->exactly(1)->literal('x')->getGrammar(),
                ['x'],
                new Node('one', 0, 1, 'x', [
                    new Node('', 0, 1, 'x')
                ])
            ],
            'exactly three "x" with "xxx"' => [
                Builder::create()->rule('three')->exactly(3)->literal('x')->getGrammar(),
                ['xxx'],
                new Node('three', 0, 3, 'xxx', [
                    new Node('', 0, 1, 'xxx'),
                    new Node('', 1, 2, 'xxx'),
                    new Node('', 2, 3, 'xxx'),
                ])
            ],
            // range of occurences, min > 0, max is finite
            'between one and three "x" with "x"' => [
                Builder::create()->rule('1..3')->between(1, 3)->literal('x')->getGrammar(),
                ['x'],
                new Node('1..3', 0, 1, 'x', [
                    new Node('', 0, 1, 'x'),
                ])
            ],
            'between one and three "x" with "xxx"' => [
                Builder::create()->rule('1..3')->between(1, 3)->literal('x')->getGrammar(),
                ['xxx'],
                new Node('1..3', 0, 3, 'xxx', [
                    new Node('', 0, 1, 'xxx'),
                    new Node('', 1, 2, 'xxx'),
                    new Node('', 2, 3, 'xxx'),
                ])
            ],
            // range of occurences, min > 0, max is infinite
            'one or more "x" with "xxx"' => [
                Builder::create()->rule('+')->q(1, INF)->literal('x')->getGrammar(),
                ['xxx'],
                new Node('+', 0, 3, 'xxx', [
                    new Node('', 0, 1, 'xxx'),
                    new Node('', 1, 2, 'xxx'),
                    new Node('', 2, 3, 'xxx'),
                ])
            ],
            // range of occurences, min === 0
            'optional "x" with "foo"' => [
                Builder::create()->rule('?')->q(0, 1)->literal('x')->getGrammar(),
                ['foo'],
                new Node('?', 0, 0, 'foo', [])
            ],
            '0 or more "x" with "foo"' => [
                Builder::create()->rule('*')->q(0, INF)->literal('x')->getGrammar(),
                ['foo'],
                new Node('*', 0, 0, 'foo', [])
            ],
            '0 or more "x" with "xoo"' => [
                Builder::create()->rule('*')->q(0, INF)->literal('x')->getGrammar(),
                ['xoo'],
                new Node('*', 0, 1, 'xoo', [new Node('', 0, 1, 'xoo')])
            ],
        ];
    }

    /**
     * @dataProvider testMatchErrorProvider
     * @expectedException \ju1ius\Pegasus\Exception\ParseError
     */
    public function testMatchError($expr, $match_args)
    {
        $this->parse($expr, ...$match_args);
    }
    public function testMatchErrorProvider()
    {
        return [
            'exactly one "x" with "foo"' => [
                Builder::create()->rule('one')->exactly(1)->literal('x')->getGrammar(),
                ['foo']
            ],
            '2 or more "x" with "x_x"' => [
                Builder::create()->rule('two')->q(2, INF)->literal('x')->getGrammar(),
                ['x_x']
            ]
        ];
    }

}
