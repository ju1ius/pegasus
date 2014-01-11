<?php

use ju1ius\Test\Pegasus\ExpressionTestCase;

use ju1ius\Pegasus\Expression\OneOrMore;
use ju1ius\Pegasus\Expression\Literal;
use ju1ius\Pegasus\Node\Terminal as Term;
use ju1ius\Pegasus\Node\Composite as Comp;


class OneOrMoreTest extends ExpressionTestCase
{
    /**
     * @dataProvider testMatchProvider
     */
    public function testMatch($members, $match_args, $expected)
    {
        $expr = new OneOrMore($members);
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
                new Comp('', 'xxx', 0, 3, [
                    new Term('', 'xxx', 0, 1),
                    new Term('', 'xxx', 1, 2),
                    new Term('', 'xxx', 2, 3),
                ])
            ],
        ];
    }

    /**
     * @dataProvider testMatchErrorProvider
     * @expectedException ju1ius\Pegasus\Exception\ParseError
     */
    public function testMatchError($members, $match_args)
    {
        $expr = new OneOrMore($members);
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
