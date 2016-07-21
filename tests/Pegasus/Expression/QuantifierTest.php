<?php

namespace ju1ius\Pegasus\Tests\Expression;

use ju1ius\Pegasus\GrammarBuilder;
use ju1ius\Pegasus\Node;
use ju1ius\Pegasus\Node\Terminal;
use ju1ius\Pegasus\Tests\ExpressionTestCase;

class QuantifierTest extends ExpressionTestCase
{
    /**
     * @dataProvider getMatchProvider
     */
    public function testMatch($expr, $match_args, $expected)
    {
        $this->assertNodeEquals(
            $expected,
            $this->parse($expr, ...$match_args)
        );
    }
    public function getMatchProvider()
    {
        return [
            // exact number of occurences
            'exactly one "x" with "x"' => [
                GrammarBuilder::create()->rule('one')->exactly(1)->literal('x')->getGrammar(),
                ['x'],
                new Node\Quantifier('one', 0, 1, [
                    new Terminal('', 0, 1, 'x')
                ])
            ],
            'exactly three "x" with "xxx"' => [
                GrammarBuilder::create()->rule('three')->exactly(3)->literal('x')->getGrammar(),
                ['xxx'],
                new Node\Quantifier('three', 0, 3, [
                    new Terminal('', 0, 1, 'x'),
                    new Terminal('', 1, 2, 'x'),
                    new Terminal('', 2, 3, 'x'),
                ])
            ],
            // range of occurences, min > 0, max is finite
            'between one and three "x" with "x"' => [
                GrammarBuilder::create()->rule('1..3')->between(1, 3)->literal('x')->getGrammar(),
                ['x'],
                new Node\Quantifier('1..3', 0, 1, [
                    new Terminal('', 0, 1, 'x'),
                ])
            ],
            'between one and three "x" with "xxx"' => [
                GrammarBuilder::create()->rule('1..3')->between(1, 3)->literal('x')->getGrammar(),
                ['xxx'],
                new Node\Quantifier('1..3', 0, 3, [
                    new Terminal('', 0, 1, 'x'),
                    new Terminal('', 1, 2, 'x'),
                    new Terminal('', 2, 3, 'x'),
                ])
            ],
            // range of occurences, min > 0, max is infinite
            'one or more "x" with "xxx"' => [
                GrammarBuilder::create()->rule('+')->q(1, INF)->literal('x')->getGrammar(),
                ['xxx'],
                new Node\Quantifier('+', 0, 3, [
                    new Terminal('', 0, 1, 'x'),
                    new Terminal('', 1, 2, 'x'),
                    new Terminal('', 2, 3, 'x'),
                ])
            ],
            // range of occurences, min === 0
            'optional "x" with "foo"' => [
                GrammarBuilder::create()->rule('?')->q(0, 1)->literal('x')->getGrammar(),
                ['foo'],
                new Node\Quantifier('?', 0, 0, [], true)
            ],
            '0 or more "x" with "foo"' => [
                GrammarBuilder::create()->rule('*')->q(0, INF)->literal('x')->getGrammar(),
                ['foo'],
                new Node\Quantifier('*', 0, 0, [])
            ],
            '0 or more "x" with "xoo"' => [
                GrammarBuilder::create()->rule('*')->q(0, INF)->literal('x')->getGrammar(),
                ['xoo'],
                new Node\Quantifier('*', 0, 1, [new Terminal('', 0, 1, 'x')])
            ],
        ];
    }

    /**
     * @dataProvider getMatchErrorProvider
     * @expectedException \ju1ius\Pegasus\Parser\Exception\ParseError
     */
    public function testMatchError($expr, $match_args)
    {
        $this->parse($expr, ...$match_args);
    }
    public function getMatchErrorProvider()
    {
        return [
            'exactly one "x" with "foo"' => [
                GrammarBuilder::create()->rule('one')->exactly(1)->literal('x')->getGrammar(),
                ['foo']
            ],
            '2 or more "x" with "x_x"' => [
                GrammarBuilder::create()->rule('two')->q(2, INF)->literal('x')->getGrammar(),
                ['x_x']
            ]
        ];
    }

}
