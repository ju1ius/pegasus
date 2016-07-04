<?php

namespace ju1ius\Pegasus\Tests\Expression;

use ju1ius\Pegasus\Tests\ExpressionTestCase;

use ju1ius\Pegasus\Expression\Not;
use ju1ius\Pegasus\Expression\Literal;

use ju1ius\Pegasus\Node\Not as NotNode;


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
                new NotNode('not', 'barbaz', 0, 0, [])
            ],
            [
                [new Literal('bar')],
                ['foobar'],
                new NotNode('not', 'foobar', 0, 0, [])
            ],
            [
                [new Literal('foo')],
                ['foobar', 3],
                new NotNode('not', 'foobar', 3, 3, [])
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
