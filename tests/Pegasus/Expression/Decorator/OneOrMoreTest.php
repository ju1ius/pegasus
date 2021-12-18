<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests\Expression\Decorator;

use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\CST\Node\Quantifier;
use ju1ius\Pegasus\CST\Node\Terminal;
use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Decorator\OneOrMore;
use ju1ius\Pegasus\Expression\Terminal\Literal;
use ju1ius\Pegasus\Parser\Exception\ParseError;
use ju1ius\Pegasus\Tests\ExpressionTestCase;
use ju1ius\Pegasus\Tests\PegasusAssert;

class OneOrMoreTest extends ExpressionTestCase
{
    /**
     * @dataProvider provideTestMatch
     */
    public function testMatch(Expression $child, array $args, Node $expected)
    {
        $expr = new OneOrMore($child, '+');
        PegasusAssert::nodeEquals(
            $expected,
            self::parse($expr, ...$args)
        );
    }
    public function provideTestMatch(): \Traversable
    {
        yield [
            new Literal('x'),
            ['xxx'],
            new Quantifier('+', 0, 3, [
                new Terminal('', 0, 1, 'x'),
                new Terminal('', 1, 2, 'x'),
                new Terminal('', 2, 3, 'x'),
            ])
        ];
    }

    /**
     * @dataProvider provideTestMatchError
     */
    public function testMatchError(Expression $child, array $args)
    {
        $expr = new OneOrMore($child, '+');
        $this->expectException(ParseError::class);
        self::parse($expr, ...$args);
    }
    public function provideTestMatchError(): \Traversable
    {
        yield [
            new Literal('foo'),
            ['barbaz'],
        ];
    }
}
