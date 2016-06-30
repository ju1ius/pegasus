<?php

namespace ju1ius\Pegasus\Tests\Optimization;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Literal;
use ju1ius\Pegasus\Expression\OneOf;
use ju1ius\Pegasus\Optimization\FlattenChoice;

class FlattenChoiceTest extends OptimizationTestCase
{
    /**
     * @dataProvider testApplyProvider
     */
    public function testApply($input, Expression $expected)
    {
        $result = $this->applyOptimization(new FlattenChoice, $input);
        $this->assertExpressionEquals($expected, $result);
        $this->assertEquals($expected->asRightHandSide(), $result->asRightHandSide());
    }
    public function testApplyProvider()
    {
        return [
            // (("foo" | "bar") | "baz") | "w00t" => "foo" | "bar" | "baz" | "w00t"
            [
                new OneOf([
                    new OneOf([
                        new OneOf([
                            new Literal('foo'),
                            new Literal('bar'),
                        ]),
                        new Literal('baz')
                    ]),
                    new Literal('w00t')
                ], 'test'),
                new OneOf([
                    new Literal('foo'),
                    new Literal('bar'),
                    new Literal('baz'),
                    new Literal('w00t')
                ], 'test')
            ],
            // "foo" | ("bar" | ("baz" | "w00t")) => "foo" | "bar" | "baz" | "w00t"
            [
                new OneOf([
                    new Literal('foo'),
                    new OneOf([
                        new Literal('bar'),
                        new OneOf([
                            new Literal('baz'),
                            new Literal('w00t'),
                        ]),
                    ]),
                ], 'test'),
                new OneOf([
                    new Literal('foo'),
                    new Literal('bar'),
                    new Literal('baz'),
                    new Literal('w00t')
                ], 'test')
            ],
        ];
    }
}
