<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests\Expression\Combinator;

use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\CST\Node\Decorator;
use ju1ius\Pegasus\CST\Node\Terminal;
use ju1ius\Pegasus\Expression\Combinator\OneOf;
use ju1ius\Pegasus\Expression\Terminal\Literal;
use ju1ius\Pegasus\GrammarBuilder;
use ju1ius\Pegasus\Tests\ExpressionTestCase;

class OneOfTest extends ExpressionTestCase
{
    /**
     * @dataProvider provideTestMatch
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
    public function provideTestMatch()
    {
        yield 'Returns true with no capturing children' => [
            GrammarBuilder::create()->rule('test')->oneOf()
                ->ignore()->literal('foo')
                ->ignore()->literal('bar')
                ->getGrammar(),
            ['bar'],
            true
        ];
        yield 'Lifts the first matching result if it is not a grammar rule.' => [
            GrammarBuilder::create()->rule('test')->oneOf()
                ->literal('bar')
                ->literal('foo')
                ->getGrammar(),
            ['foo'],
            new Terminal('test', 0, 3, 'foo')
        ];
        yield 'Decorates the first matching result if is a grammar rule.' => [
            GrammarBuilder::create()
                ->rule('test')->oneOf()
                    ->literal('bar')
                    ->ref('foo')
                ->rule('foo')->literal('foo')
                ->getGrammar(),
            ['foo'],
            new Decorator('test', 0, 3, new Terminal('foo', 0, 3, 'foo'))
        ];
    }

    /**
     * @dataProvider provideTestMatchError
     * @expectedException \ju1ius\Pegasus\Parser\Exception\ParseError
     */
    public function testMatchError($children, $match_args)
    {
        $expr = new OneOf($children, 'choice');
        $this->parse($expr, ...$match_args);
    }
    public function provideTestMatchError()
    {
        yield [
            [new Literal('foo'), new Literal('doh')],
            ['barbaz'],
        ];
    }

}
