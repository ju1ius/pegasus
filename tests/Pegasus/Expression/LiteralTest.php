<?php

namespace ju1ius\Pegasus\Tests\Expression;

use ju1ius\Pegasus\Expression\Literal;
use ju1ius\Pegasus\Node\Literal as Node;
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
            ['foo', ['foo'], new Node('test', 'foo', 0, 3)],
            ['foo', ['foobar'], new Node('test', 'foobar', 0, 3)],
            ['foo', ['barfoo', 3], new Node('test', 'barfoo', 3, 6)],
            ['bar', ['foobarbaz', 3], new Node('test', 'foobarbaz', 3, 6)],
        ];
    }
}
