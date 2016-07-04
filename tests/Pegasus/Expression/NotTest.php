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
                new Node('not', 0, 0, 'barbaz', [])
            ],
            [
                [new Literal('bar')],
                ['foobar'],
                new Node('not', 0, 0, 'foobar', [])
            ],
            [
                [new Literal('foo')],
                ['foobar', 3],
                new Node('not', 3, 3, 'foobar', [])
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
