<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests\Expression\Terminal;

use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\CST\Node\Terminal;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\GrammarBuilder;
use ju1ius\Pegasus\Tests\ExpressionTestCase;
use ju1ius\Pegasus\Tests\PegasusAssert;

class MatchTest extends ExpressionTestCase
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
    public function provideTestMatch(): iterable
    {
        // [ [pattern(,name(,flags))], text, [name, text, start, end, children, matches] ]
        // simple literals
        yield '/foo/ with "foo"' => [
            GrammarBuilder::create()->rule('r')->match('foo')->getGrammar(),
            ['foo'],
            new Terminal('r', 0, 3, 'foo')
        ];
        yield '/bar/ @3 with "foobar"' => [
            GrammarBuilder::create()->rule('r')->match('bar')->getGrammar(),
            ['foobar', 3],
            new Terminal('r', 3, 6, 'bar')
        ];
        yield '/fo+/ with "fooooobar!"' => [
            GrammarBuilder::create()->rule('r')->match('fo+')->getGrammar(),
            ['fooooobar!'],
            new Terminal('r', 0, 6, 'fooooo')
        ];
    }
}
