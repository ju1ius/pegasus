<?php

use ju1ius\Test\Pegasus\ExpressionTestCase;

use ju1ius\Pegasus\Expression\OneOf;
use ju1ius\Pegasus\Expression\Literal;

use ju1ius\Pegasus\Node\OneOf as OneOfNode;
use ju1ius\Pegasus\Node\Literal as LiteralNode;


class OneOfTest extends ExpressionTestCase
{
    /**
     * @dataProvider testMatchProvider
     */
    public function testMatch($children, $match_args, $expected)
    {
        $expr = new OneOf($children);
        $this->assertNodeEquals(
            $expected,
            call_user_func_array([$this, 'parse'], array_merge([$expr], $match_args))
        );
    }
    public function testMatchProvider()
    {
        return [
            [
                [new Literal('bar'), new Literal('foo')],
                ['foobar'],
                new OneOfNode('', 'foobar', 0, 3, [
                    new LiteralNode('', 'foobar', 0, 3),
                ])
            ],
            # must return the first matched expression
            [
                [new Literal('foo', 'FOO'), new Literal('foo', 'FOO2')],
                ['foobar'],
                new OneOfNode('', 'foobar', 0, 3, [
                    new LiteralNode('FOO', 'foobar', 0, 3),
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
        $expr = new OneOf($children);
        call_user_func_array([$this, 'parse'], array_merge([$expr], $match_args));
    }
    public function testMatchErrorProvider()
    {
        return [
            [
                [new Literal('foo'), new Literal('doh')],
                ['barbaz'],
            ]
        ];
    }
    
}
