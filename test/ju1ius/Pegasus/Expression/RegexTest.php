<?php

require_once __DIR__.'/../ExpressionBase_TestCase.php';

use ju1ius\Pegasus\Expression\Regex;
use ju1ius\Pegasus\Node\Regex as Node;


class RegexTest extends ExpressionBase_TestCase
{
    /**
     * @dataProvider testMatchProvider
     */
    public function testMatch($args, $match_args, $expected)
    {
        $expr = $this->expr('Regex', $args);
        $this->assertEquals(
            $expected,
            call_user_func_array([$expr, 'match'], $match_args)
        );
    }
    public function testMatchProvider()
    {
        // [ [pattern(,name(,flags))], text, [name, text, start, end, children, matches] ]
        return [
            // simple literals
            [['foo'], ['foo'], new Node('', 'foo', 0, 3, [], ['foo'])],
            [['bar'], ['foobar', 3], new Node('', 'foobar', 3, 6, [], ['bar'])],

            [['fo+'], ['fooooobar!'], new Node('', 'fooooobar!', 0, 6, [], ['fooooo'])],
            [
                ['"((?:(?:\\\\.)|[^"])*)"'],
                ['"quoted\\"stri\\ng"'],
                new Node('', '"quoted\\"stri\\ng"', 0, 17, [], [
                    '"quoted\\"stri\\ng"',
                    'quoted\\"stri\\ng'
                ])
            ],
        ];
    }
}
