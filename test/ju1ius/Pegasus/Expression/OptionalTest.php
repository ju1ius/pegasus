<?php

require_once __DIR__.'/../ExpressionBase_TestCase.php';

use ju1ius\Pegasus\Expression\Optional;
use ju1ius\Pegasus\Expression\Literal;
use ju1ius\Pegasus\Expression\Regex;
use ju1ius\Pegasus\Node;
use ju1ius\Pegasus\Node\Regex as RegexNode;


class OptionalTest extends ExpressionBase_TestCase
{
    /**
     * @dataProvider testMatchProvider
     */
    public function testMatch($members, $match_args, $expected)
    {
        $expr = new Optional($members);
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
                ['foo'],
                new Node('', 'foo', 0, 3, [new Node('', 'foo', 0, 3)])
            ],
            [
                [new Literal('foo')],
                ['bar'],
                new Node('', 'bar', 0, 0)
            ],
            [
                [new Regex('[\w-]+')],
                ['d-o_0-b'],
                new Node('', 'd-o_0-b', 0, 7, [
                    new RegexNode('', 'd-o_0-b', 0, 7, [], ['d-o_0-b'])]
                )
            ],
            [
                [new Regex('[\w-]+')],
                ['$_o_$'],
                new Node('', '$_o_$', 0, 0)
            ],
            [
                [new Regex('[\w-]+')],
                ['micro$oft'],
                new Node('', 'micro$oft', 0, 5, [
                    new RegexNode('', 'micro$oft', 0, 5, [], ['micro'])
                ])
            ],
        ];
    }
}
