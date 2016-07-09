<?php

namespace ju1ius\Pegasus\Tests\Expression;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Literal;
use ju1ius\Pegasus\Expression\Match;
use ju1ius\Pegasus\Expression\Optional;
use ju1ius\Pegasus\Expression\RegExp;
use ju1ius\Pegasus\Node;
use ju1ius\Pegasus\Tests\ExpressionTestCase;

class OptionalTest extends ExpressionTestCase
{
    /**
     * @dataProvider testMatchProvider
     */
    public function testMatch(Expression $child, array $match_args, Node $expected)
    {
        $expr = new Optional($child, '?');
        $this->assertNodeEquals(
            $expected,
            $this->parse($expr, ...$match_args)
        );
    }
    public function testMatchProvider()
    {
        return [
            [
                new Literal('foo'),
                ['foo'],
                new Node('?', 0, 3, null, [new Node('', 0, 3, 'foo')])
            ],
            [
                new Literal('foo'),
                ['bar'],
                new Node('?', 0, 0)
            ],
            [
                new Match('[\w-]+'),
                ['d-o_0-b'],
                new Node('?', 0, 7, null, [
					new Node('', 0, 7, 'd-o_0-b')]
                )
            ],
            [
                new Match('[\w-]+'),
                ['$_o_$'],
                new Node('?', 0, 0)
            ],
            [
                new Match('[\w-]+'),
                ['micro$oft'],
                new Node('?', 0, 5, null, [
					new Node('', 0, 5, 'micro')
                ])
            ],
        ];
    }
}
