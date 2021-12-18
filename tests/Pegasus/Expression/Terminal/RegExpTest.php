<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests\Expression\Terminal;

use ju1ius\Pegasus\CST\Node\Terminal;
use ju1ius\Pegasus\GrammarBuilder;
use ju1ius\Pegasus\Tests\ExpressionTestCase;
use ju1ius\Pegasus\Tests\PegasusAssert;

class RegExpTest extends ExpressionTestCase
{
    /**
     * @dataProvider provideTestMatch
     */
    public function testMatch($expr, $match_args, $expected)
    {
        PegasusAssert::nodeEquals(
            $expected,
            self::parse($expr, ...$match_args)
        );
    }
    public function provideTestMatch(): \Traversable
    {
        // simple literals
        yield '/foo/ with "foo"' => [
            GrammarBuilder::create()->rule('r')->regexp('foo')->getGrammar(),
            ['foo'],
            new Terminal('r', 0, 3, 'foo', ['groups' => ['foo']])
        ];
        yield '/bar/ @3 with "foobar"' => [
            GrammarBuilder::create()->rule('r')->regexp('bar')->getGrammar(),
            ['foobar', 3],
            new Terminal('r', 3, 6, 'bar', ['groups' => ['bar']])
        ];
        yield '/fo+/ with "fooooobar!"' => [
            GrammarBuilder::create()->rule('r')->regexp('fo+')->getGrammar(),
            ['fooooobar!'],
            new Terminal('r', 0, 6, 'fooooo', ['groups' => ['fooooo']])
        ];
        yield 'complex pattern with capturing groups' => [
            GrammarBuilder::create()->rule('r')->regexp('"((?:\\\\.|[^"])*)"')->getGrammar(),
            ['"quoted\\"stri\\ng"'],
            new Terminal('r', 0, 17, '"quoted\\"stri\\ng"', ['groups' => [
                '"quoted\\"stri\\ng"',
                'quoted\\"stri\\ng'
            ]])
        ];
    }
}
