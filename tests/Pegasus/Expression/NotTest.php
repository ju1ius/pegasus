<?php

namespace ju1ius\Pegasus\Tests\Expression;

use ju1ius\Pegasus\Tests\ExpressionTestCase;

use ju1ius\Pegasus\Expression\Not;
use ju1ius\Pegasus\Expression\Literal;

use ju1ius\Pegasus\Node\Not as NotNode;


class NotTest extends ExpressionTestCase
{
    /**
     * @dataProvider testMatchProvider
     */
    public function testMatch($children, $match_args, $expected)
    {
        $expr = new Not($children);
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
                ['barbaz'],
                new NotNode('', 'barbaz', 0, 0, [])
            ],
            [
                [new Literal('bar')],
                ['foobar'],
                new NotNode('', 'foobar', 0, 0, [])
            ],
            [
                [new Literal('foo')],
                ['foobar', 3],
                new NotNode('', 'foobar', 3, 3, [])
            ],
        ];
    }

    /**
     * @dataProvider testMatchErrorProvider
     * @expectedException ju1ius\Pegasus\Exception\ParseError
     */
    public function testMatchError($children, $match_args)
    {
        $expr = new Not($children);
        call_user_func_array([$this, 'parse'], array_merge([$expr], $match_args));
    }
    public function testMatchErrorProvider()
    {
        return [
            [
                [new Literal('bar')],
                ['barbaz']
            ]
        ];
    }

}
