<?php

namespace ju1ius\Pegasus\Tests\Expression;

use ju1ius\Pegasus\Grammar\Builder;
use ju1ius\Pegasus\Node;
use ju1ius\Pegasus\Node\Terminal;
use ju1ius\Pegasus\Tests\ExpressionTestCase;

class MatchTest extends ExpressionTestCase
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
        // [ [pattern(,name(,flags))], text, [name, text, start, end, children, matches] ]
        return [
            // simple literals
			'/foo/ with "foo"' => [
                Builder::create()->rule('r')->match('foo')->getGrammar(),
                ['foo'],
                new Terminal('r', 0, 3, 'foo')
            ],
            '/bar/ @3 with "foobar"' => [
                Builder::create()->rule('r')->match('bar')->getGrammar(),
                ['foobar', 3],
                new Terminal('r', 3, 6, 'bar')
            ],
			'/fo+/ with "fooooobar!"' => [
                Builder::create()->rule('r')->match('fo+')->getGrammar(),
                ['fooooobar!'],
                new Terminal('r', 0, 6, 'fooooo')
            ],
        ];
    }
}
