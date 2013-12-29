<?php

require_once __DIR__.'/../ExpressionBase_TestCase.php';

use ju1ius\Pegasus\Expression\OneOrMore;
use ju1ius\Pegasus\Expression\Literal;
use ju1ius\Pegasus\Node;
use ju1ius\Pegasus\Node\Regex as RegexNode;


class OneOrMoreTest extends ExpressionBase_TestCase
{
    /**
     * @dataProvider testMatchProvider
     */
    public function testMatch($members, $match_args, $expected)
    {
        $expr = new OneOrMore($members);
        $this->assertEquals(
            call_user_func_array([$expr, 'match'], $match_args),
            $expected
        );
    }
    public function testMatchProvider()
    {
        return [
            [
                [new Literal('x')],
                ['xxx'],
                new Node('', 'xxx', 0, 3, [
                    new Node('', 'xxx', 0, 1),
                    new Node('', 'xxx', 1, 2),
                    new Node('', 'xxx', 2, 3),
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
                ['barbaz'],
            ]
        ];
    }
}
