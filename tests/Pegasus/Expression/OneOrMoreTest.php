<?php

use ju1ius\Test\Pegasus\ExpressionTestCase;

use ju1ius\Pegasus\Expression\OneOrMore;
use ju1ius\Pegasus\Expression\Literal;

use ju1ius\Pegasus\Node\Literal as Lit;
use ju1ius\Pegasus\Node\Quantifier as Quant;


class OneOrMoreTest extends ExpressionTestCase
{
    /**
     * @dataProvider testMatchProvider
     */
    public function testMatch($children, $match_args, $expected)
    {
        $expr = new OneOrMore($children);
        $this->assertNodeEquals(
            $expected,
            call_user_func_array([$this, 'parse'], array_merge([$expr], $match_args))
        );
    }
    public function testMatchProvider()
    {
        return [
            [
                [new Literal('x')],
                ['xxx'],
                new Quant('', 'xxx', 0, 3, [
                    new Lit('', 'xxx', 0, 1),
                    new Lit('', 'xxx', 1, 2),
                    new Lit('', 'xxx', 2, 3),
                ])
            ],
        ];
    }

    /**
     * @dataProvider testMatchErrorProvider
     * @expectedException ju1ius\Pegasus\Exception\ParseError
     */
    public function testMatchError($children, $match_args)
    {
        $expr = new OneOrMore($children);
        call_user_func_array([$this, 'parse'], array_merge([$expr], $match_args));
    }
    public function testMatchErrorProvider()
    {
        return [
            [
                [new Literal('foo')],
                ['barbaz'],
            ]
        ];
    }
}
