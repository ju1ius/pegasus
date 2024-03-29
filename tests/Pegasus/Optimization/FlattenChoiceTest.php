<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests\Optimization;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Combinator\OneOf;
use ju1ius\Pegasus\Expression\Terminal\Literal;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\Optimization\Flattening\FlattenChoice;
use ju1ius\Pegasus\GrammarBuilder;
use ju1ius\Pegasus\Tests\PegasusAssert;
use PHPUnit\Framework\Assert;

class FlattenChoiceTest extends OptimizationTestCase
{
    /**
     * @dataProvider provideTestApply
     *
     * @param Grammar    $grammar
     * @param Expression $expected
     */
    public function testApply(Grammar $grammar, Expression $expected)
    {
        $result = $this->applyOptimization(new FlattenChoice(), $grammar);
        PegasusAssert::ExpressionEquals($expected, $result);
        Assert::assertEquals((string)$expected, (string)$result);
    }

    public function provideTestApply()
    {
        yield '(("foo" | "bar") | "baz") | "qux" => "foo" | "bar" | "baz" | "qux"' => [
            GrammarBuilder::create()->rule('test')->oneOf()
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
        ];
        yield '"foo" | ("bar" | ("baz" | "qux")) => "foo" | "bar" | "baz" | "qux"' => [
            GrammarBuilder::create()->rule('test')->oneOf()
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
        ];
    }
}
