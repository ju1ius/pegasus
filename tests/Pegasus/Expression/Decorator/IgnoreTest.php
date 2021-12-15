<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests\Expression\Decorator;

use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\CST\Node\Terminal;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\GrammarBuilder;
use ju1ius\Pegasus\Tests\ExpressionTestCase;

class IgnoreTest extends ExpressionTestCase
{
    /**
     * @dataProvider provideTestMatch
     *
     * @param Grammar   $grammar
     * @param array     $args
     * @param Node|bool $expected
     */
    public function testMatch(Grammar $grammar, array $args, $expected)
    {
        $result = $this->parse($grammar, ...$args);
        if ($expected instanceof Node) {
            $this->assertNodeEquals($expected, $result);
        } else {
            $this->assertSame($expected, $result);
        }
    }

    public function provideTestMatch()
    {
        yield 'returns true' => [
            GrammarBuilder::create()->rule('nope')->ignore()->literal('nope')->getGrammar(),
            ['nope'],
            true
        ];
        yield 'skip parenthesis around (foo)' => [
            GrammarBuilder::create()->rule('start')->seq()
                ->ignore()->literal('(')
                ->literal('foo')
                ->ignore()->literal(')')
                ->getGrammar(),
            ['(foo)'],
            new Terminal('start', 1, 4, 'foo')
        ];
        yield 'skip choice result at sequence start' => [
            GrammarBuilder::create()->rule('start')->seq()
                ->ignore()->oneOf()
                    ->literal('€')
                    ->literal('$')
                    ->literal('£')
                ->end()
                ->literal('42')
                ->getGrammar(),
            ['$42'],
            new Terminal('start', 1, 3, '42')
        ];
    }
}
