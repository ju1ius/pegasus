<?php

namespace ju1ius\Pegasus\Tests\Expression;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Literal;
use ju1ius\Pegasus\Expression\OneOrMore;
use ju1ius\Pegasus\Node;
use ju1ius\Pegasus\Node\Quantifier;
use ju1ius\Pegasus\Node\Terminal;
use ju1ius\Pegasus\Tests\ExpressionTestCase;

class OneOrMoreTest extends ExpressionTestCase
{
    /**
     * @dataProvider testMatchProvider
     */
    public function testMatch(Expression $child, array $match_args, Node $expected)
    {
        $expr = new OneOrMore($child, '+');
        $this->assertNodeEquals(
            $expected,
            $this->parse($expr, ...$match_args)
        );
    }
    public function testMatchProvider()
    {
        return [
            [
                new Literal('x'),
                ['xxx'],
                new Quantifier('+', 0, 3, [
                    new Terminal('', 0, 1, 'x'),
                    new Terminal('', 1, 2, 'x'),
                    new Terminal('', 2, 3, 'x'),
                ])
            ],
        ];
    }

    /**
     * @dataProvider testMatchErrorProvider
     * @expectedException \ju1ius\Pegasus\Exception\ParseError
     */
    public function testMatchError(Expression $child, array $match_args)
    {
        $expr = new OneOrMore($child, '+');
        $this->parse($expr, ...$match_args);
    }
    public function testMatchErrorProvider()
    {
        return [
            [
                new Literal('foo'),
                ['barbaz'],
            ]
        ];
    }
}
