<?php

namespace ju1ius\Pegasus\Tests\Optimization;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Literal;
use ju1ius\Pegasus\Expression\Sequence;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\Builder;
use ju1ius\Pegasus\Optimization\FlattenCapturingSequence;
use ju1ius\Pegasus\Optimization\FlattenMatchingSequence;
use ju1ius\Pegasus\Optimization\FlattenSequence;

class FlattenSequenceTest extends OptimizationTestCase
{
    /**
     * @dataProvider testApplyProvider
     *
     * @param Grammar $grammar
     * @param Expression $expected
     */
    public function testApply(Grammar $grammar, Expression $expected)
    {
        $optim = new FlattenSequence(
            new FlattenMatchingSequence(),
            new FlattenCapturingSequence()
        );
        $result = $this->applyOptimization($optim, $grammar);
        $this->assertExpressionEquals($expected, $result);
        $this->assertEquals((string)$expected, (string)$result);
    }
    public function testApplyProvider()
    {
        return [
            // (("foo" "bar") "baz") "w00t" => "foo" "bar" "baz" "w00t"
            '(("foo" "bar") "baz") "qux" => "foo" "bar" "baz" "qux"' => [
                Builder::create()->rule('test')->seq()
                    ->seq()
                        ->seq()
                            ->literal('foo')
                            ->literal('bar')
                        ->end()
                        ->literal('baz')
                    ->end()
                    ->literal('qux')
                ->getGrammar(),
                new Sequence([
                    new Literal('foo'),
                    new Literal('bar'),
                    new Literal('baz'),
                    new Literal('qux')
                ], 'test')
            ],
            '"foo" ("bar" ("baz" "qux")) => "foo" "bar" "baz" "qux"' => [
                Builder::create()->rule('test')->seq()
                    ->literal('foo')
                    ->seq()
                        ->literal('bar')
                        ->seq()
                            ->literal('baz')
                            ->literal('qux')
                ->getGrammar(),
                new Sequence([
                    new Literal('foo'),
                    new Literal('bar'),
                    new Literal('baz'),
                    new Literal('qux')
                ], 'test')
            ],
        ];
    }
}
