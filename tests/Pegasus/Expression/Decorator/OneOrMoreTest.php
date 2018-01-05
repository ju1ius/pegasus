<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests\Expression\Decorator;

use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\CST\Node\Quantifier;
use ju1ius\Pegasus\CST\Node\Terminal;
use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Decorator\OneOrMore;
use ju1ius\Pegasus\Expression\Terminal\Literal;
use ju1ius\Pegasus\Tests\ExpressionTestCase;

class OneOrMoreTest extends ExpressionTestCase
{
    /**
     * @dataProvider getMatchProvider
     */
    public function testMatch(Expression $child, array $match_args, Node $expected)
    {
        $expr = new OneOrMore($child, '+');
        $this->assertNodeEquals(
            $expected,
            $this->parse($expr, ...$match_args)
        );
    }
    public function getMatchProvider()
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
     * @dataProvider getMatchErrorProvider
     * @expectedException \ju1ius\Pegasus\Parser\Exception\ParseError
     */
    public function testMatchError(Expression $child, array $match_args)
    {
        $expr = new OneOrMore($child, '+');
        $this->parse($expr, ...$match_args);
    }
    public function getMatchErrorProvider()
    {
        return [
            [
                new Literal('foo'),
                ['barbaz'],
            ]
        ];
    }
}
