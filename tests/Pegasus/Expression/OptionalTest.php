<?php

namespace ju1ius\Pegasus\Tests\Expression;

use ju1ius\Pegasus\Expression\Literal;
use ju1ius\Pegasus\Expression\Optional;
use ju1ius\Pegasus\Expression\RegExp;
use ju1ius\Pegasus\Node;
use ju1ius\Pegasus\Tests\ExpressionTestCase;

class OptionalTest extends ExpressionTestCase
{
    /**
     * @dataProvider testMatchProvider
     */
    public function testMatch($children, $match_args, $expected)
    {
        $expr = new Optional($children, '?');
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
                ['foo'],
                new Node('?', 0, 3, 'foo', [new Node('', 0, 3, 'foo')])
            ],
            [
                [new Literal('foo')],
                ['bar'],
                new Node('?', 0, 0, 'bar', [])
            ],
            [
                [new RegExp('[\w-]+')],
                ['d-o_0-b'],
                new Node('?', 0, 7, 'd-o_0-b', [
					new Node('', 0, 7, 'd-o_0-b', ['d-o_0-b'])]
                )
            ],
            [
                [new RegExp('[\w-]+')],
                ['$_o_$'],
                new Node('?', 0, 0, '$_o_$', [])
            ],
            [
                [new RegExp('[\w-]+')],
                ['micro$oft'],
                new Node('?', 0, 5, 'micro$oft', [
					new Node('', 0, 5, 'micro$oft', ['micro'])
                ])
            ],
        ];
    }
}
