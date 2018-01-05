<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests\Optimization;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Terminal\Literal;
use ju1ius\Pegasus\Expression\Combinator\Sequence;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\GrammarBuilder;
use ju1ius\Pegasus\Grammar\Optimization\Flattening\FlattenCapturingSequence;
use ju1ius\Pegasus\Grammar\Optimization\Flattening\FlattenMatchingSequence;
use ju1ius\Pegasus\Grammar\Optimization\FlattenSequence;

class FlattenSequenceTest extends OptimizationTestCase
{
    /**
     * @dataProvider getApplyProvider
     *
     * @param Grammar $grammar
     * @param Expression $expected
     */
    public function testApply(Grammar $grammar, Expression $expected)
    {
        $optim = [
            new FlattenMatchingSequence(),
            new FlattenCapturingSequence()
        ];
        $result = $this->applyOptimization($optim, $grammar);
        $this->assertExpressionEquals($expected, $result);
        $this->assertEquals((string)$expected, (string)$result);
    }
    public function getApplyProvider()
    {
        return [
            // (("foo" "bar") "baz") "w00t" => "foo" "bar" "baz" "w00t"
            '(("foo" "bar") "baz") "qux" => "foo" "bar" "baz" "qux"' => [
                GrammarBuilder::create()->rule('test')->seq()
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
                GrammarBuilder::create()->rule('test')->seq()
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
