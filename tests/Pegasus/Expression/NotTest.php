<?php

namespace ju1ius\Pegasus\Tests\Expression;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Literal;
use ju1ius\Pegasus\Expression\Not;
use ju1ius\Pegasus\Node;
use ju1ius\Pegasus\Node\Transient;
use ju1ius\Pegasus\Tests\ExpressionTestCase;

class NotTest extends ExpressionTestCase
{
    /**
     * @dataProvider testMatchProvider
     */
    public function testMatch(Expression $child, $args, $expected)
    {
        $expr = new Not($child, 'not');
        $this->assertNodeEquals(
            $expected,
            $this->parse($expr, ...$args)
        );
    }
    public function testMatchProvider()
    {
        return [
            [
                new Literal('foo'),
                ['barbaz'],
                new Transient(0, 0)
            ],
            [
                new Literal('bar'),
                ['foobar'],
                new Transient(0, 0)
            ],
            [
                new Literal('foo'),
                ['foobar', 3],
                new Transient(3, 3)
            ],
        ];
    }

    /**
     * @dataProvider testMatchErrorProvider
     * @expectedException \ju1ius\Pegasus\Exception\ParseError
     */
    public function testMatchError(Expression $child, $args)
    {
        $expr = new Not($child, 'not');
        $this->parse($expr, ...$args);
    }
    public function testMatchErrorProvider()
    {
        return [
            [
                new Literal('bar'),
                ['barbaz']
            ]
        ];
    }

}
