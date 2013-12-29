<?php

require_once __DIR__.'/../ExpressionBase_TestCase.php';

use ju1ius\Pegasus\Expression\Sequence;
use ju1ius\Pegasus\Expression\Literal;
use ju1ius\Pegasus\Node;
use ju1ius\Pegasus\Node\Regex as RegexNode;


class SequenceTest extends ExpressionBase_TestCase
{
    /**
     * @dataProvider testMatchProvider
     */
    public function testMatch($members, $match_args, $expected)
    {
        $expr = new Sequence($members);
        $this->assertEquals(
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
                new Node('', 'foobar', 0, 6, [
                    new Node('', 'foobar', 0, 3),
                    new Node('', 'foobar', 3, 6),
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
        $expr = new Sequence($members);
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
