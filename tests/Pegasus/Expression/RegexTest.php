<?php

namespace ju1ius\Pegasus\Tests\Expression;

use ju1ius\Pegasus\Grammar\Builder;
use ju1ius\Pegasus\Node;
use ju1ius\Pegasus\Tests\ExpressionTestCase;

class RegexTest extends ExpressionTestCase
{
    /**
     * @dataProvider testMatchProvider
     */
    public function testMatch($expr, $match_args, $expected)
    {
        $this->assertNodeEquals(
            $expected,
            $this->parse($expr, ...$match_args)
        );
    }
    public function testMatchProvider()
    {
        // [ [pattern(,name(,flags))], text, [name, text, start, end, children, matches] ]
        return [
            // simple literals
			'/foo/ with "foo"' => [
                Builder::create()->rule('r')->match('foo')->getGrammar(),
                ['foo'],
                new Node('r', 0, 3, 'foo', ['foo'])
            ],
            '/bar/ @3 with "foobar"' => [
                Builder::create()->rule('r')->match('bar')->getGrammar(),
                ['foobar', 3],
                new Node('r', 3, 6, 'foobar', ['bar'])
            ],
			'/fo+/ with "fooooobar!"' => [
                Builder::create()->rule('r')->match('fo+')->getGrammar(),
                ['fooooobar!'],
                new Node('r', 0, 6, 'fooooobar!', ['fooooo'])
            ],
            'complex pattern with capturing groups' => [
                Builder::create()->rule('r')->match('"((?:(?:\\\\.)|[^"])*)"')->getGrammar(),
                ['"quoted\\"stri\\ng"'],
				new Node('r', 0, 17, '"quoted\\"stri\\ng"', [
                    '"quoted\\"stri\\ng"',
                    'quoted\\"stri\\ng'
                ])
            ],
        ];
    }
}
