<?php

namespace ju1ius\Pegasus\Tests\Expression;

use ju1ius\Pegasus\Expression\Literal;
use ju1ius\Pegasus\Expression\Not;
use ju1ius\Pegasus\Node;
use ju1ius\Pegasus\Tests\ExpressionTestCase;

class NotTest extends ExpressionTestCase
{
    /**
     * @dataProvider testMatchProvider
     */
    public function testMatch($children, $args, $expected)
    {
        $expr = new Not($children, 'not');
        $this->assertNodeEquals(
            $expected,
            $this->parse($expr, ...$args)
        );
    }
    public function testMatchProvider()
    {
        return [
            [
                [new Literal('foo')],
                ['barbaz'],
                Node::transient('not', 0, 0)
            ],
            [
                [new Literal('bar')],
                ['foobar'],
                Node::transient('not', 0, 0)
            ],
            [
                [new Literal('foo')],
                ['foobar', 3],
                Node::transient('not', 3, 3)
            ],
        ];
    }

    /**
     * @dataProvider testMatchErrorProvider
     * @expectedException \ju1ius\Pegasus\Exception\ParseError
     */
    public function testMatchError($children, $args)
    {
        $expr = new Not($children, 'not');
        $this->parse($expr, ...$args);
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
