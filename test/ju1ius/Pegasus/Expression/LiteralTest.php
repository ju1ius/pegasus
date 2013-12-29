<?php

require_once __DIR__.'/../../../Pegasus_TestCase.php';

use ju1ius\Pegasus\Expression\Literal;
use ju1ius\Pegasus\Node;


class LiteralTest extends Pegasus_TestCase
{
    /**
     * @dataProvider testMatchProvider
     */
    public function testMatch($literal, $params, $expected)
    {
        $expr = new Literal($literal);
        $this->assertEquals(
            $expected,
            call_user_func_array([$expr, 'match'], $params)
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
