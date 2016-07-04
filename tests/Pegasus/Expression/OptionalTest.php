<?php

namespace ju1ius\Pegasus\Tests\Expression;

use ju1ius\Pegasus\Expression\Literal;
use ju1ius\Pegasus\Expression\Optional;
use ju1ius\Pegasus\Expression\Regex;
use ju1ius\Pegasus\Node\Literal as Lit;
use ju1ius\Pegasus\Node\Quantifier as Quant;
use ju1ius\Pegasus\Node\Regex as Rx;
use ju1ius\Pegasus\Tests\ExpressionTestCase;

class OptionalTest extends ExpressionTestCase
{
    /**
     * @dataProvider testMatchProvider
     */
    public function testMatch($children, $match_args, $expected)
    {
        $expr = new Optional($children, '?');
        $this->assertNodeEquals(
            $expected,
            $this->parse($expr, ...$match_args)
        );
    }
    public function testMatchProvider()
    {
        return [
            [
                [new Literal('foo')],
                ['foo'],
                new Quant('?', 'foo', 0, 3, [new Lit('', 'foo', 0, 3)])
            ],
            [
                [new Literal('foo')],
                ['bar'],
                new Quant('?', 'bar', 0, 0, [])
            ],
            [
                [new Regex('[\w-]+')],
                ['d-o_0-b'],
                new Quant('?', 'd-o_0-b', 0, 7, [
					new Rx('', 'd-o_0-b', 0, 7, ['d-o_0-b'])]
                )
            ],
            [
                [new Regex('[\w-]+')],
                ['$_o_$'],
                new Quant('?', '$_o_$', 0, 0, [])
            ],
            [
                [new Regex('[\w-]+')],
                ['micro$oft'],
                new Quant('?', 'micro$oft', 0, 5, [
					new Rx('', 'micro$oft', 0, 5, ['micro'])
                ])
            ],
        ];
    }
}
