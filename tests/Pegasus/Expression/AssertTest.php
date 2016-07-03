<?php

namespace ju1ius\Pegasus\Tests\Expression;

use ju1ius\Pegasus\Tests\ExpressionTestCase;

use ju1ius\Pegasus\Expression\Assert;
use ju1ius\Pegasus\Expression\Literal;

use ju1ius\Pegasus\Node\Assert as LA;


class AssertTest extends ExpressionTestCase
{
    /**
     * @dataProvider testMatchProvider
     */
    public function testMatch(array $children, array $match_args, $expected)
    {
        $expr = new Assert($children);
        $this->assertNodeEquals(
            $expected,
            $this->parse($expr, ...$match_args)
        );
    }
    public function testMatchProvider()
    {
        return [
            [
                [new Literal('foo')],
                ['foobar'],
                new LA('', 'foobar', 0, 0, [])
            ],
            [
                [new Literal('bar')],
                ['foobar', 3],
                new LA('', 'foobar', 3, 3, [])
            ],
        ];
    }

    /**
     * @dataProvider testMatchErrorProvider
     * @expectedException \ju1ius\Pegasus\Exception\ParseError
     *
     * @param array $children
     * @param array $match_args
     */
    public function testMatchError(array $children, array $match_args)
    {
        $expr = new Assert($children);
        $this->parse($expr, ...$match_args);
    }
    public function testMatchErrorProvider()
    {
        return [
            [
                [new Literal('foo')],
                ['barbaz']
            ]
        ];
    }

}
