<?php

namespace ju1ius\Pegasus\Tests\Expression;

use ju1ius\Pegasus\Grammar\Builder;
use ju1ius\Pegasus\Node\Regex as Rx;
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
                Builder::create()->rule('r')->regex('foo')->getGrammar(),
                ['foo'],
                new Rx('r', 'foo', 0, 3, ['foo'])
            ],
            '/bar/ @3 with "foobar"' => [
                Builder::create()->rule('r')->regex('bar')->getGrammar(),
                ['foobar', 3],
                new Rx('r', 'foobar', 3, 6, ['bar'])
            ],
			'/fo+/ with "fooooobar!"' => [
                Builder::create()->rule('r')->regex('fo+')->getGrammar(),
                ['fooooobar!'],
                new Rx('r', 'fooooobar!', 0, 6, ['fooooo'])
            ],
            'complex pattern with capturing groups' => [
                Builder::create()->rule('r')->regex('"((?:(?:\\\\.)|[^"])*)"')->getGrammar(),
                ['"quoted\\"stri\\ng"'],
				new Rx('r', '"quoted\\"stri\\ng"', 0, 17, [
                    '"quoted\\"stri\\ng"',
                    'quoted\\"stri\\ng'
                ])
            ],
        ];
    }
}
