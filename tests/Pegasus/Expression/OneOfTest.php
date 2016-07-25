<?php

namespace ju1ius\Pegasus\Tests\Expression;

use ju1ius\Pegasus\Expression\Combinator\OneOf;
use ju1ius\Pegasus\Expression\Terminal\Literal;
use ju1ius\Pegasus\GrammarBuilder;
use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\CST\Node\Decorator;
use ju1ius\Pegasus\CST\Node\Terminal;
use ju1ius\Pegasus\Tests\ExpressionTestCase;

class OneOfTest extends ExpressionTestCase
{
    /**
     * @dataProvider getMatchProvider
     */
    public function testMatch($expr, $args, $expected)
    {
        $result = $this->parse($expr, ...$args);
        if ($expected instanceof Node) {
            $this->assertNodeEquals($expected, $result);
        } else {
            $this->assertSame($expected, $result);
        }
    }
    public function getMatchProvider()
    {
        return [
            'Returns true with no capturing children' => [
                GrammarBuilder::create()->rule('test')->oneOf()
                    ->skip()->literal('foo')
                    ->skip()->literal('bar')
                    ->getGrammar(),
                ['bar'],
                true
            ],
            'Lifts the first matching result if it is not a grammar rule.' => [
                GrammarBuilder::create()->rule('test')->oneOf()
                    ->literal('bar')
                    ->literal('foo')
                    ->getGrammar(),
                ['foo'],
                new Terminal('test', 0, 3, 'foo')
            ],
            'Decorates the first matching result if is a grammar rule.' => [
                GrammarBuilder::create()
                    ->rule('test')->oneOf()
                        ->literal('bar')
                        ->ref('foo')
                    ->rule('foo')->literal('foo')
                    ->getGrammar(),
                ['foo'],
                new Decorator('test', 0, 3, new Terminal('foo', 0, 3, 'foo'))
            ],
        ];
    }

    /**
     * @dataProvider getMatchErrorProvider
     * @expectedException \ju1ius\Pegasus\Parser\Exception\ParseError
     */
    public function testMatchError($children, $match_args)
    {
        $expr = new OneOf($children, 'choice');
        $this->parse($expr, ...$match_args);
    }
    public function getMatchErrorProvider()
    {
        return [
            [
                [new Literal('foo'), new Literal('doh')],
                ['barbaz'],
            ]
        ];
    }

}
