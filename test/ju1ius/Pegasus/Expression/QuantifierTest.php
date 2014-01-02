<?php

require_once __DIR__.'/../ExpressionBase_TestCase.php';

use ju1ius\Pegasus\Expression\Quantifier;
use ju1ius\Pegasus\Expression\Literal;
use ju1ius\Pegasus\Node\Terminal as Term;
use ju1ius\Pegasus\Node\Composite as Comp;


class QuantifierTest extends ExpressionBase_TestCase
{
    /**
     * @dataProvider testMatchProvider
     */
    public function testMatch($args, $match_args, $expected)
    {
        $expr = $this->expr('Quantifier', $args);
        $this->assertNodeEquals(
            $expected,
            call_user_func_array([$this, 'parse'], array_merge([$expr], $match_args))
        );
    }
    public function testMatchProvider()
    {
        return [
            // exact number of occurences
            [
                [[new Literal('x')], '', 1, 1],
                ['x'],
                new Comp('', 'x', 0, 1, [new Term('', 'x', 0, 1)])
            ],
            [
                [[new Literal('x')], '', 3, 3],
                ['xxx'],
                new Comp('', 'xxx', 0, 3, [
                    new Term('', 'xxx', 0, 1),
                    new Term('', 'xxx', 1, 2),
                    new Term('', 'xxx', 2, 3),
                ])
            ],
            // range of occurences, min > 0, max is finite
            [
                [[new Literal('x')], '', 1, 3],
                ['x'],
                new Comp('', 'x', 0, 1, [
                    new Term('', 'x', 0, 1),
                ])
            ],
            [
                [[new Literal('x')], '', 1, 3],
                ['xxx'],
                new Comp('', 'xxx', 0, 3, [
                    new Term('', 'xxx', 0, 1),
                    new Term('', 'xxx', 1, 2),
                    new Term('', 'xxx', 2, 3),
                ])
            ],
            // range of occurences, min > 0, max is infinite
            [
                [[new Literal('x')], '', 1, null],
                ['xxx'],
                new Comp('', 'xxx', 0, 3, [
                    new Term('', 'xxx', 0, 1),
                    new Term('', 'xxx', 1, 2),
                    new Term('', 'xxx', 2, 3),
                ])
            ],
            // range of occurences, min === 0
            [
                [[new Literal('x')], '', 0, 1],
                ['foo'],
                new Comp('', 'foo', 0, 0, [])
            ],
            [
                [[new Literal('x')], '', 0, null],
                ['foo'],
                new Comp('', 'foo', 0, 0, [])
            ],
            [
                [[new Literal('x')], '', 0, null],
                ['xoo'],
                new Comp('', 'xoo', 0, 1, [new Term('', 'xoo', 0, 1)])
            ],
        ];
    }

    /**
     * @dataProvider testMatchErrorProvider
     * @expectedException ju1ius\Pegasus\Exception\ParseError
     */
    public function testMatchError($args, $match_args)
    {
        $expr = $this->expr('Quantifier', $args);
        call_user_func_array([$this, 'parse'], array_merge([$expr], $match_args));
    }
    public function testMatchErrorProvider()
    {
        return [
            [
                [[new Literal('x')], '', 1, null],
                ['foo']
            ],
            [
                [[new Literal('x')], '', 2, null],
                ['x_x']
            ]
        ];
    }
    
}
