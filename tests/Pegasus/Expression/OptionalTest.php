<?php

namespace ju1ius\Pegasus\Tests\Expression;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Terminal\Literal;
use ju1ius\Pegasus\Expression\Terminal\Match;
use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\CST\Node\Quantifier;
use ju1ius\Pegasus\CST\Node\Terminal;
use ju1ius\Pegasus\Tests\ExpressionTestCase;

class OptionalTest extends ExpressionTestCase
{
    /**
     * @dataProvider getMatchProvider
     */
    public function testMatch(Expression $child, array $match_args, Node $expected)
    {
        $expr = new Expression\Decorator\Optional($child, '?');
        $this->assertNodeEquals(
            $expected,
            $this->parse($expr, ...$match_args)
        );
    }
    public function getMatchProvider()
    {
        return [
            [
                new Literal('foo'),
                ['foo'],
                new Quantifier('?', 0, 3, [new Terminal('', 0, 3, 'foo')], true)
            ],
            [
                new Literal('foo'),
                ['bar'],
                new Quantifier('?', 0, 0, [], true)
            ],
            [
                new Match('[\w-]+'),
                ['d-o_0-b'],
                new Quantifier('?', 0, 7, [new Terminal('', 0, 7, 'd-o_0-b')], true)
            ],
            [
                new Match('[\w-]+'),
                ['$_o_$'],
                new Quantifier('?', 0, 0, [], true)
            ],
            [
                new Match('[\w-]+'),
                ['micro$oft'],
                new Quantifier('?', 0, 5, [new Terminal('', 0, 5, 'micro')], true)
            ],
        ];
    }
}
