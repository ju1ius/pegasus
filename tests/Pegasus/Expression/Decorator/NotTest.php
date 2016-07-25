<?php

namespace ju1ius\Pegasus\Tests\Expression\Decorator;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Decorator\Not;
use ju1ius\Pegasus\Expression\Terminal\Literal;
use ju1ius\Pegasus\Tests\ExpressionTestCase;

class NotTest extends ExpressionTestCase
{
    /**
     * @dataProvider getMatchProvider
     */
    public function testMatch(Expression $child, $args, $expected)
    {
        $expr = new Not($child, 'not');
        $this->assertParseResult($expected, $expr, ...$args);
    }
    public function getMatchProvider()
    {
        return [
            [
                new Literal('foo'),
                ['barbaz'],
                true
            ],
            [
                new Literal('bar'),
                ['foobar'],
                true
            ],
            [
                new Literal('foo'),
                ['foobar', 3],
                true
            ],
        ];
    }

    /**
     * @dataProvider getMatchErrorProvider
     * @expectedException \ju1ius\Pegasus\Parser\Exception\ParseError
     */
    public function testMatchError(Expression $child, $args)
    {
        $expr = new Not($child, 'not');
        $this->parse($expr, ...$args);
    }
    public function getMatchErrorProvider()
    {
        return [
            [
                new Literal('bar'),
                ['barbaz']
            ]
        ];
    }

}
