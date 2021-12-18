<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests\Expression\Decorator;

use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\CST\Node\Quantifier;
use ju1ius\Pegasus\CST\Node\Terminal;
use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Terminal\Literal;
use ju1ius\Pegasus\Expression\Terminal\RegExp;
use ju1ius\Pegasus\Tests\ExpressionTestCase;
use ju1ius\Pegasus\Tests\PegasusAssert;

class OptionalTest extends ExpressionTestCase
{
    /**
     * @dataProvider provideTestMatch
     */
    public function testMatch(Expression $child, array $args, Node $expected)
    {
        $expr = new Expression\Decorator\Optional($child, '?');
        PegasusAssert::nodeEquals(
            $expected,
            self::parse($expr, ...$args),
            (string)$expected,
        );
    }

    public function provideTestMatch(): iterable
    {
        yield [
            new Literal('foo'),
            ['foo'],
            new Quantifier('?', 0, 3, [new Terminal('', 0, 3, 'foo')], true),
        ];
        yield [
            new Literal('foo'),
            ['bar'],
            new Quantifier('?', 0, 0, [], true),
        ];
        yield [
            new RegExp('[\w-]+'),
            ['d-o_0-b'],
            new Quantifier('?', 0, 7, [new Terminal('', 0, 7, 'd-o_0-b')], true),
        ];
        yield [
            new RegExp('[\w-]+'),
            ['$_o_$'],
            new Quantifier('?', 0, 0, [], true),
        ];
        yield [
            new RegExp('[\w-]+'),
            ['micro$oft'],
            new Quantifier('?', 0, 5, [new Terminal('', 0, 5, 'micro')], true),
        ];
    }
}
