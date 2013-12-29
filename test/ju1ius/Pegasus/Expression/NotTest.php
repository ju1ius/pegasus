<?php

require_once __DIR__.'/../ExpressionBase_TestCase.php';

use ju1ius\Pegasus\Expression\Not;
use ju1ius\Pegasus\Expression\Literal;
use ju1ius\Pegasus\Node;


class NotTest extends ExpressionBase_TestCase
{
    /**
     * @dataProvider testMatchProvider
     */
    public function testMatch($members, $match_args, $expected)
    {
        $expr = new Not($members);
        $this->assertEquals(
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
                new Node('', 'barbaz', 0, 0)
            ],
            [
                [new Literal('bar')],
                ['foobar'],
                new Node('', 'foobar', 0, 0)
            ],
            [
                [new Literal('foo')],
                ['foobar', 3],
                new Node('', 'foobar', 3, 3)
            ],
        ];
    }

    /**
     * @dataProvider testMatchErrorProvider
     * @expectedException ju1ius\Pegasus\Exception\ParseError
     */
    public function testMatchError($members, $match_args)
    {
        $expr = new Not($members);
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
