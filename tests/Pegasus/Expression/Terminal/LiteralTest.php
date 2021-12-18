<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests\Expression\Terminal;

use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\CST\Node\Terminal;
use ju1ius\Pegasus\Expression\Terminal\Literal;
use ju1ius\Pegasus\Tests\ExpressionTestCase;
use ju1ius\Pegasus\Tests\PegasusAssert;

class LiteralTest extends ExpressionTestCase
{
    /**
     * @dataProvider provideTestMatch
     */
    public function testMatch(string $literal, array $params, Node $expected)
    {
        $expr = new Literal($literal, 'test');
        PegasusAssert::nodeEquals($expected, self::parse($expr, ...$params));
    }

    public function provideTestMatch(): \Traversable
    {
        yield ['foo', ['foo'], new Terminal('test', 0, 3, 'foo')];
        yield ['foo', ['foobar'], new Terminal('test', 0, 3, 'foo')];
        yield ['foo', ['barfoo', 3], new Terminal('test', 3, 6, 'foo')];
        yield ['bar', ['foobarbaz', 3], new Terminal('test', 3, 6, 'bar')];
    }
}
