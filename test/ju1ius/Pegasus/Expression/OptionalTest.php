<?php

require_once __DIR__.'/../ExpressionBase_TestCase.php';

use ju1ius\Pegasus\Expression\Optional;
use ju1ius\Pegasus\Expression\Literal;
use ju1ius\Pegasus\Expression\Regex;
use ju1ius\Pegasus\Node\Regex as Rx;
use ju1ius\Pegasus\Node\Terminal as Term;
use ju1ius\Pegasus\Node\Composite as Comp;


class OptionalTest extends ExpressionBase_TestCase
{
    /**
     * @dataProvider testMatchProvider
     */
    public function testMatch($members, $match_args, $expected)
    {
        $expr = new Optional($members);
        $this->assertNodeEquals(
            $expected,
            call_user_func_array([$this, 'parse'], array_merge([$expr], $match_args))
        );
    }
    public function testMatchProvider()
    {
        return [
            [
                [new Literal('foo')],
                ['foo'],
                new Comp('', 'foo', 0, 3, [new Term('', 'foo', 0, 3)])
            ],
            [
                [new Literal('foo')],
                ['bar'],
                new Comp('', 'bar', 0, 0, [])
            ],
            [
                [new Regex('[\w-]+')],
                ['d-o_0-b'],
                new Comp('', 'd-o_0-b', 0, 7, [
					new Rx('', 'd-o_0-b', 0, 7, ['d-o_0-b'])]
                )
            ],
            [
                [new Regex('[\w-]+')],
                ['$_o_$'],
                new Comp('', '$_o_$', 0, 0, [])
            ],
            [
                [new Regex('[\w-]+')],
                ['micro$oft'],
                new Comp('', 'micro$oft', 0, 5, [
					new Rx('', 'micro$oft', 0, 5, ['micro'])
                ])
            ],
        ];
    }
}
