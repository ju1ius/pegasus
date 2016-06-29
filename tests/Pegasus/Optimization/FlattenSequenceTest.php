<?php

namespace ju1ius\Pegasus\Tests\Optimization;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Optimization\FlattenSequence;
use ju1ius\Pegasus\Optimization\FlattenMatchingSequence;
use ju1ius\Pegasus\Optimization\FlattenCapturingSequence;
use ju1ius\Pegasus\Expression\Sequence;
use ju1ius\Pegasus\Expression\Literal;


class FlattenSequenceTest extends OptimizationTestCase
{
    /**
     * @dataProvider testApplyProvider
     */
    public function testApply($input, Expression $expected)
    {
        $opt = new FlattenSequence(
            new FlattenMatchingSequence,
            new FlattenCapturingSequence
        );
        $result = $this->apply($opt, $input);
        $this->assertExpressionEquals($expected, $result);
        $this->assertEquals($expected->asRightHandSide(), $result->asRightHandSide());
    }
    public function testApplyProvider()
    {
        return [
            // (("foo" "bar") "baz") "w00t" => "foo" "bar" "baz" "w00t"
            [
                new Sequence([
                    new Sequence([
                        new Sequence([
                            new Literal('foo'),
                            new Literal('bar'),
                        ]),
                        new Literal('baz')
                    ]),
                    new Literal('w00t')
                ], 'test'),
                new Sequence([
                    new Literal('foo'),
                    new Literal('bar'),
                    new Literal('baz'),
                    new Literal('w00t')
                ], 'test')
            ],
            // "foo" ("bar" ("baz" "w00t")) => "foo" "bar" "baz" "w00t"
            [
                new Sequence([
                    new Literal('foo'),
                    new Sequence([
                        new Literal('bar'),
                        new Sequence([
                            new Literal('baz'),
                            new Literal('w00t'),
                        ]),
                    ]),
                ], 'test'),
                new Sequence([
                    new Literal('foo'),
                    new Literal('bar'),
                    new Literal('baz'),
                    new Literal('w00t')
                ], 'test')
            ],
        ];
    }
}
