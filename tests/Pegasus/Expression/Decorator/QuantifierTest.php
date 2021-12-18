<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests\Expression\Decorator;

use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\CST\Node\Terminal;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\GrammarBuilder;
use ju1ius\Pegasus\Parser\Exception\ParseError;
use ju1ius\Pegasus\Tests\ExpressionTestCase;
use ju1ius\Pegasus\Tests\PegasusAssert;

class QuantifierTest extends ExpressionTestCase
{
    /**
     * @dataProvider provideTestMatch
     */
    public function testMatch(Grammar $expr, array $args, Node $expected)
    {
        PegasusAssert::nodeEquals(
            $expected,
            self::parse($expr, ...$args)
        );
    }
    public function provideTestMatch(): \Traversable
    {
        // exact number of occurences
        yield 'exactly one "x" with "x"' => [
            GrammarBuilder::create()->rule('one')->exactly(1)->literal('x')->getGrammar(),
            ['x'],
            new Node\Quantifier('one', 0, 1, [
                new Terminal('', 0, 1, 'x')
            ])
        ];
        yield 'exactly three "x" with "xxx"' => [
            GrammarBuilder::create()->rule('three')->exactly(3)->literal('x')->getGrammar(),
            ['xxx'],
            new Node\Quantifier('three', 0, 3, [
                new Terminal('', 0, 1, 'x'),
                new Terminal('', 1, 2, 'x'),
                new Terminal('', 2, 3, 'x'),
            ])
        ];
        // range of occurences, min > 0, max is finite
        yield 'between one and three "x" with "x"' => [
            GrammarBuilder::create()->rule('1..3')->between(1, 3)->literal('x')->getGrammar(),
            ['x'],
            new Node\Quantifier('1..3', 0, 1, [
                new Terminal('', 0, 1, 'x'),
            ])
        ];
        yield 'between one and three "x" with "xxx"' => [
            GrammarBuilder::create()->rule('1..3')->between(1, 3)->literal('x')->getGrammar(),
            ['xxx'],
            new Node\Quantifier('1..3', 0, 3, [
                new Terminal('', 0, 1, 'x'),
                new Terminal('', 1, 2, 'x'),
                new Terminal('', 2, 3, 'x'),
            ])
        ];
        // range of occurences, min > 0, max is infinite
        yield 'one or more "x" with "xxx"' => [
            GrammarBuilder::create()->rule('+')->atLeast(1)->literal('x')->getGrammar(),
            ['xxx'],
            new Node\Quantifier('+', 0, 3, [
                new Terminal('', 0, 1, 'x'),
                new Terminal('', 1, 2, 'x'),
                new Terminal('', 2, 3, 'x'),
            ])
        ];
        // range of occurences, min === 0
        yield 'optional "x" with "foo"' => [
            GrammarBuilder::create()->rule('?')->q(0, 1)->literal('x')->getGrammar(),
            ['foo'],
            new Node\Quantifier('?', 0, 0, [], true)
        ];
        yield '0 or more "x" with "foo"' => [
            GrammarBuilder::create()->rule('*')->atLeast(0)->literal('x')->getGrammar(),
            ['foo'],
            new Node\Quantifier('*', 0, 0, [])
        ];
        yield '0 or more "x" with "xoo"' => [
            GrammarBuilder::create()->rule('*')->atLeast(0)->literal('x')->getGrammar(),
            ['xoo'],
            new Node\Quantifier('*', 0, 1, [new Terminal('', 0, 1, 'x')])
        ];
    }

    /**
     * @dataProvider provideTestMatchError
     */
    public function testMatchError(Grammar $expr, array $args)
    {
        $this->expectException(ParseError::class);
        self::parse($expr, ...$args);
    }
    public function provideTestMatchError(): \Traversable
    {
        yield 'exactly one "x" with "foo"' => [
            GrammarBuilder::create()->rule('one')->exactly(1)->literal('x')->getGrammar(),
            ['foo']
        ];
        yield '2 or more "x" with "x_x"' => [
            GrammarBuilder::create()->rule('two')->atLeast(2)->literal('x')->getGrammar(),
            ['x_x']
        ];
    }

}
