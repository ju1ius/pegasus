<?php

require_once __DIR__.'/../ExpressionBase_TestCase.php';

use ju1ius\Pegasus\Expression\Regex;
use ju1ius\Pegasus\Node\Regex as Rx;


class RegexTest extends ExpressionBase_TestCase
{
    /**
     * @dataProvider testMatchProvider
     */
    public function testMatch($args, $match_args, $expected)
    {
        $expr = $this->expr('Regex', $args);
        $this->assertNodeEquals(
            $expected,
            call_user_func_array([$this, 'parse'], array_merge([$expr], $match_args))
        );
    }
    public function testMatchProvider()
    {
        // [ [pattern(,name(,flags))], text, [name, text, start, end, children, matches] ]
        return [
            // simple literals
			[['foo'], ['foo'], new Rx('', 'foo', 0, 3, ['foo'])],
			[['bar'], ['foobar', 3], new Rx('', 'foobar', 3, 6, ['bar'])],

			[['fo+'], ['fooooobar!'], new Rx('', 'fooooobar!', 0, 6, ['fooooo'])],
            [
                ['"((?:(?:\\\\.)|[^"])*)"'],
                ['"quoted\\"stri\\ng"'],
				new Rx('', '"quoted\\"stri\\ng"', 0, 17, [
                    '"quoted\\"stri\\ng"',
                    'quoted\\"stri\\ng'
                ])
            ],
        ];
    }
}
