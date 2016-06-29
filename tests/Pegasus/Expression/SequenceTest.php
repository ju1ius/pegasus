<?php

namespace ju1ius\Pegasus\Tests\Expression;

use ju1ius\Pegasus\Expression\Literal;
use ju1ius\Pegasus\Expression\Sequence;
use ju1ius\Pegasus\Node\Literal as Lit;
use ju1ius\Pegasus\Node\Sequence as Seq;
use ju1ius\Pegasus\Tests\ExpressionTestCase;

class SequenceTest extends ExpressionTestCase
{
    /**
     * @dataProvider testMatchProvider
     */
    public function testMatch($children, $match_args, $expected)
    {
        $expr = new Sequence($children);
        $this->assertNodeEquals(
            $expected,
            call_user_func_array([$this, 'parse'], array_merge([$expr], $match_args))
        );
    }
    public function testMatchProvider()
    {
        return [
            [
                [new Literal('foo'), new Literal('bar')],
                ['foobar'],
                new Seq('', 'foobar', 0, 6, [
                    new Lit('', 'foobar', 0, 3),
                    new Lit('', 'foobar', 3, 6),
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
        $expr = new Sequence($children);
        call_user_func_array([$this, 'parse'], array_merge([$expr], $match_args));
    }
    public function testMatchErrorProvider()
    {
        return [
            [
                [new Literal('foo'), new Literal('bar')],
                ['barbaz'],
            ]
        ];
    }

}
