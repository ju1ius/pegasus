<?php

namespace ju1ius\Pegasus\Tests\Expression;

use ju1ius\Pegasus\Tests\ExpressionTestCase;

use ju1ius\Pegasus\Expression\Lookahead;
use ju1ius\Pegasus\Expression\Literal;

use ju1ius\Pegasus\Node\Lookahead as LA;


class LookaheadTest extends ExpressionTestCase
{
    /**
     * @dataProvider testMatchProvider
     */
    public function testMatch($children, $match_args, $expected)
    {
        $expr = new Lookahead($children);
        $this->assertNodeEquals(
            $expected,
            call_user_func_array([$this, 'parse'], array_merge([$expr], $match_args))
        );
    }
    public function testMatchProvider()
    {
        return [
            [
                [new Literal('foo')],
                ['foobar'],
                new LA('', 'foobar', 0, 0, [])
            ],
            [
                [new Literal('bar')],
                ['foobar', 3],
                new LA('', 'foobar', 3, 3, [])
            ],
        ];
    }

    /**
     * @dataProvider testMatchErrorProvider
     * @expectedException ju1ius\Pegasus\Exception\ParseError
     */
    public function testMatchError($children, $match_args)
    {
        $expr = new Lookahead($children);
        call_user_func_array([$this, 'parse'], array_merge([$expr], $match_args));
    }
    public function testMatchErrorProvider()
    {
        return [
            [
                [new Literal('foo')],
                ['barbaz']
            ]
        ];
    }

}
