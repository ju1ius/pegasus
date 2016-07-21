<?php

namespace ju1ius\Pegasus\Tests\Expression;

use ju1ius\Pegasus\Expression\Combinator\OneOf;
use ju1ius\Pegasus\Expression\Terminal\Literal;
use ju1ius\Pegasus\GrammarBuilder;
use ju1ius\Pegasus\Node;
use ju1ius\Pegasus\Node\Decorator;
use ju1ius\Pegasus\Node\Terminal;
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
            'Returns the first matching result' => [
                GrammarBuilder::create()->rule('test')->oneOf()
                    ->literal('bar')
                    ->literal('foo')
                    ->getGrammar(),
                ['foobar'],
                new Decorator('test', 0, 3, new Terminal('', 0, 3, 'foo'))
            ],
            'With no capturing expressions' => [
                GrammarBuilder::create()->rule('test')->oneOf()
                    ->skip()->literal('foo')
                    ->skip()->literal('bar')
                    ->getGrammar(),
                ['bar'],
                true
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
