<?php

namespace ju1ius\Pegasus\Tests\Optimization;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Literal;
use ju1ius\Pegasus\Expression\OneOf;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\Builder;
use ju1ius\Pegasus\Optimization\FlattenChoice;

class FlattenChoiceTest extends OptimizationTestCase
{
    /**
     * @dataProvider getApplyProvider
     *
     * @param Grammar    $grammar
     * @param Expression $expected
     */
    public function testApply(Grammar $grammar, Expression $expected)
    {
        $result = $this->applyOptimization(new FlattenChoice(), $grammar);
        $this->assertExpressionEquals($expected, $result);
        $this->assertEquals((string)$expected, (string)$result);
    }

    public function getApplyProvider()
    {
        return [
            '(("foo" | "bar") | "baz") | "qux" => "foo" | "bar" | "baz" | "qux"' => [
                Builder::create()->rule('test')->oneOf()
                    ->oneOf()
                        ->oneOf()
                            ->literal('foo')
                            ->literal('bar')
                        ->end()
                        ->literal('baz')
                    ->end()
                    ->literal('qux')
                ->getGrammar(),
                new OneOf([
                    new Literal('foo'),
                    new Literal('bar'),
                    new Literal('baz'),
                    new Literal('qux'),
                ], 'test')
            ],
            '"foo" | ("bar" | ("baz" | "qux")) => "foo" | "bar" | "baz" | "qux"' => [
                Builder::create()->rule('test')->oneOf()
                    ->literal('foo')
                    ->oneOf()
                        ->literal('bar')
                        ->oneOf()
                            ->literal('baz')
                            ->literal('qux')
                        ->end()
                    ->end()
                ->getGrammar(),
                new OneOf([
                    new Literal('foo'),
                    new Literal('bar'),
                    new Literal('baz'),
                    new Literal('qux')
                ], 'test')
            ],
        ];
    }
}
