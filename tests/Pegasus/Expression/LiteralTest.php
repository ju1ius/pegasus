<?php

namespace ju1ius\Pegasus\Tests\Expression;

use ju1ius\Pegasus\Expression\Literal;
use ju1ius\Pegasus\Node;
use ju1ius\Pegasus\Node\Terminal;
use ju1ius\Pegasus\Tests\ExpressionTestCase;

class LiteralTest extends ExpressionTestCase
{
    /**
     * @dataProvider testMatchProvider
     *
     * @param string $literal
     * @param array  $params
     * @param Node   $expected
     */
    public function testMatch($literal, array $params, Node $expected)
    {
        $expr = new Literal($literal, 'test');
        $this->assertNodeEquals($expected, $this->parse($expr, ...$params));
    }

    public function testMatchProvider()
    {
        return [
            ['foo', ['foo'], new Terminal('test', 0, 3, 'foo')],
            ['foo', ['foobar'], new Terminal('test', 0, 3, 'foo')],
            ['foo', ['barfoo', 3], new Terminal('test', 3, 6, 'foo')],
            ['bar', ['foobarbaz', 3], new Terminal('test', 3, 6, 'bar')],
        ];
    }
}
