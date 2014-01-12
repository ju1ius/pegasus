<?php

use ju1ius\Test\Pegasus\ExpressionTestCase;

use ju1ius\Pegasus\Expression\Lookahead;
use ju1ius\Pegasus\Expression\Literal;
use ju1ius\Pegasus\Node\Terminal as Term;
use ju1ius\Pegasus\Node\Composite as Comp;


class LookaheadTest extends ExpressionTestCase
{
    /**
     * @dataProvider testMatchProvider
     */
    public function testMatch($members, $match_args, $expected)
    {
        $expr = new Lookahead($members);
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
                new Comp('', 'foobar', 0, 0, [])
            ],
            [
                [new Literal('bar')],
                ['foobar', 3],
                new Comp('', 'foobar', 3, 3, [])
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
