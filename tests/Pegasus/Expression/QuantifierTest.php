<?php

namespace ju1ius\Pegasus\Tests\Expression;

use ju1ius\Pegasus\Expression\Literal;
use ju1ius\Pegasus\Node\Literal as Lit;
use ju1ius\Pegasus\Node\Quantifier as Quant;
use ju1ius\Pegasus\Tests\ExpressionTestCase;

class QuantifierTest extends ExpressionTestCase
{
    /**
     * @dataProvider testMatchProvider
     */
    public function testMatch($args, $match_args, $expected)
    {
        $expr = $this->expr('Quantifier', $args);
        $this->assertNodeEquals(
            $expected,
            call_user_func_array([$this, 'parse'], array_merge([$expr], $match_args))
        );
    }
    public function testMatchProvider()
    {
        return [
            // exact number of occurences
            [
                [[new Literal('x')], 1, 1],
                ['x'],
                new Quant('', 'x', 0, 1, [new Lit('', 'x', 0, 1)])
            ],
            [
                [[new Literal('x')], 3, 3],
                ['xxx'],
                new Quant('', 'xxx', 0, 3, [
                    new Lit('', 'xxx', 0, 1),
                    new Lit('', 'xxx', 1, 2),
                    new Lit('', 'xxx', 2, 3),
                ])
            ],
            // range of occurences, min > 0, max is finite
            [
                [[new Literal('x')], 1, 3],
                ['x'],
                new Quant('', 'x', 0, 1, [
                    new Lit('', 'x', 0, 1),
                ])
            ],
            [
                [[new Literal('x')], 1, 3],
                ['xxx'],
                new Quant('', 'xxx', 0, 3, [
                    new Lit('', 'xxx', 0, 1),
                    new Lit('', 'xxx', 1, 2),
                    new Lit('', 'xxx', 2, 3),
                ])
            ],
            // range of occurences, min > 0, max is infinite
            [
                [[new Literal('x')], 1, INF],
                ['xxx'],
                new Quant('', 'xxx', 0, 3, [
                    new Lit('', 'xxx', 0, 1),
                    new Lit('', 'xxx', 1, 2),
                    new Lit('', 'xxx', 2, 3),
                ])
            ],
            // range of occurences, min === 0
            [
                [[new Literal('x')], 0, 1],
                ['foo'],
                new Quant('', 'foo', 0, 0, [])
            ],
            [
                [[new Literal('x')], 0, INF],
                ['foo'],
                new Quant('', 'foo', 0, 0, [])
            ],
            [
                [[new Literal('x')], 0, INF],
                ['xoo'],
                new Quant('', 'xoo', 0, 1, [new Lit('', 'xoo', 0, 1)])
            ],
        ];
    }

    /**
     * @dataProvider testMatchErrorProvider
     * @expectedException ju1ius\Pegasus\Exception\ParseError
     */
    public function testMatchError($args, $match_args)
    {
        $expr = $this->expr('Quantifier', $args);
        call_user_func_array([$this, 'parse'], array_merge([$expr], $match_args));
    }
    public function testMatchErrorProvider()
    {
        return [
            [
                [[new Literal('x')], 1, 1],
                ['foo']
            ],
            [
                [[new Literal('x')], 2, INF],
                ['x_x']
            ]
        ];
    }

}
