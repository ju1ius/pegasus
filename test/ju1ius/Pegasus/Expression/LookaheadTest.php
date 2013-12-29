<?php

require_once __DIR__.'/../ExpressionBase_TestCase.php';

use ju1ius\Pegasus\Expression\Lookahead;
use ju1ius\Pegasus\Expression\Literal;
use ju1ius\Pegasus\Node;


class LookaheadTest extends ExpressionBase_TestCase
{
    /**
     * @dataProvider testMatchProvider
     */
    public function testMatch($members, $match_args, $expected)
    {
        $expr = new Lookahead($members);
        $this->assertEquals(
            $expected,
            call_user_func_array([$expr, 'match'], $match_args)
        );
    }
    public function testMatchProvider()
    {
        return [
            [
                [new Literal('foo')],
                ['foobar'],
                new Node('', 'foobar', 0, 0)
            ],
            [
                [new Literal('bar')],
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
        $expr = new Lookahead($members);
        $this->assertEquals(
            call_user_func_array([$expr, 'match'], $match_args),
            $expected
        );
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
