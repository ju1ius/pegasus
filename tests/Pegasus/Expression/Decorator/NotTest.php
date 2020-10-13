<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests\Expression\Decorator;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Decorator\Not;
use ju1ius\Pegasus\Expression\Terminal\Literal;
use ju1ius\Pegasus\Parser\Exception\ParseError;
use ju1ius\Pegasus\Tests\ExpressionTestCase;

class NotTest extends ExpressionTestCase
{
    /**
     * @dataProvider provideTestMatch
     */
    public function testMatch(Expression $child, $args, $expected)
    {
        $expr = new Not($child, 'not');
        $this->assertParseResult($expected, $expr, ...$args);
    }
    public function provideTestMatch()
    {
        yield [
            new Literal('foo'),
            ['barbaz'],
            true
        ];
        yield [
            new Literal('bar'),
            ['foobar'],
            true
        ];
        yield [
            new Literal('foo'),
            ['foobar', 3],
            true
        ];
    }

    /**
     * @dataProvider provideTestMatchError
     */
    public function testMatchError(Expression $child, $args)
    {
        $expr = new Not($child, 'not');
        $this->expectException(ParseError::class);
        $this->parse($expr, ...$args);
    }
    public function provideTestMatchError()
    {
        yield [
            new Literal('bar'),
            ['barbaz']
        ];
    }

}
