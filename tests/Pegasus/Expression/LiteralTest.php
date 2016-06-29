<?php

namespace ju1ius\Pegasus\Tests\Expression;

use ju1ius\Pegasus\Expression\Literal;
use ju1ius\Pegasus\Node\Literal as Node;
use ju1ius\Pegasus\Tests\ExpressionTestCase;

class LiteralTest extends ExpressionTestCase
{
    /**
     * @dataProvider testMatchProvider
     */
    public function testMatch($literal, $params, $expected)
    {
        $expr = new Literal($literal);
        $this->assertNodeEquals(
            $expected,
            call_user_func_array([$this, 'parse'], array_merge([$expr], $params))
        );
    }

    public function testMatchProvider()
    {
        return [
            ['foo', ['foo'], new Node('', 'foo', 0, 3)],
            ['foo', ['foobar'], new Node('', 'foobar', 0, 3)],
            ['foo', ['barfoo', 3], new Node('', 'barfoo', 3, 6)],
            ['bar', ['foobarbaz', 3], new Node('', 'foobarbaz', 3, 6)],
        ];
    }
}
