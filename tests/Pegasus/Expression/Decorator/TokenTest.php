<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests\Expression\Decorator;

use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\CST\Node\Terminal;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\GrammarBuilder;
use ju1ius\Pegasus\Tests\ExpressionTestCase;

class TokenTest extends ExpressionTestCase
{
    /**
     * @dataProvider provideTestMatch
     */
    public function testMatch(Grammar $grammar, array $params, Node|bool $expected)
    {
        $this->assertParseResult($expected, $grammar, ...$params);
    }

    public function provideTestMatch(): \Traversable
    {
        yield "Returns the entire string match by it's child" => [
            GrammarBuilder::create()->rule('test')->asToken()
                ->sequence()
                    ->match('\w+')
                    ->match('=')
                    ->match('\d+')
                ->getGrammar(),
            ['foo=42'],
            new Terminal('test', 0, 6, 'foo=42')
        ];
        yield "Even if the child is non-capturing" => [
            GrammarBuilder::create()->rule('test')->asToken()
                ->ignore()->match('\w+')
                ->getGrammar(),
            ['foo_bar'],
            new Terminal('test', 0, 7, 'foo_bar')
        ];
        yield "Should fail if the child fails" => [
            GrammarBuilder::create()->rule('test')->asToken()
                ->match('[a-z]+')
                ->getGrammar(),
            ['666'],
            false,
        ];
    }
}
