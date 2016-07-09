<?php

namespace ju1ius\Pegasus\Tests\Expression;

use ju1ius\Pegasus\Expression\Literal;
use ju1ius\Pegasus\Expression\OneOf;
use ju1ius\Pegasus\Node;
use ju1ius\Pegasus\Node\Decorator;
use ju1ius\Pegasus\Node\Terminal;
use ju1ius\Pegasus\Tests\ExpressionTestCase;

class OneOfTest extends ExpressionTestCase
{
    /**
     * @dataProvider testMatchProvider
     */
    public function testMatch($children, $match_args, $expected)
    {
        $expr = new OneOf($children, 'choice');
        $this->assertNodeEquals(
            $expected,
            $this->parse($expr, ...$match_args)
        );
    }
    public function testMatchProvider()
    {
        return [
            [
                [new Literal('bar'), new Literal('foo')],
                ['foobar'],
                new Decorator('choice', 0, 3, new Terminal('', 0, 3, 'foo'))
            ],
            'must return the first matched expression' => [
                [new Literal('foo', 'FOO'), new Literal('foo', 'FOO2')],
                ['foobar'],
                new Decorator('choice', 0, 3, new Terminal('FOO', 0, 3, 'foo'))
            ],
        ];
    }

    /**
     * @dataProvider testMatchErrorProvider
     * @expectedException \ju1ius\Pegasus\Exception\ParseError
     */
    public function testMatchError($children, $match_args)
    {
        $expr = new OneOf($children, 'choice');
        $this->parse($expr, ...$match_args);
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
