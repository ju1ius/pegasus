<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests\Trace;

use ju1ius\Pegasus\Expression\Combinator\OneOf;
use ju1ius\Pegasus\Expression\Decorator\Trace;
use ju1ius\Pegasus\Expression\Terminal\Literal;
use ju1ius\Pegasus\GrammarBuilder;
use ju1ius\Pegasus\Tests\PegasusTestCase;


class GrammarTracerTest extends PegasusTestCase
{
    public function testItWrapsAllExpressions()
    {
        $grammar = GrammarBuilder::create('test')
            ->rule('start')->oneOf()
                ->literal('foo')
                ->literal('bar')
            ->getGrammar()
            ->tracing();

        $expected = new Trace(
            new OneOf([
                new Trace(new Literal('foo')),
                new Trace(new Literal('bar')),
            ], 'start'),
            'start'
        );

        $this->assertExpressionEquals($expected, $grammar['start']);
    }

    public function testItRemovesTraces()
    {
        $grammar = GrammarBuilder::create('test')
            ->rule('start')->oneOf()
                ->literal('foo')
                ->literal('bar')
            ->getGrammar()
            ->tracing(true)
            ->tracing(false);

        $expected = new OneOf([
            new Literal('foo'),
            new Literal('bar'),
        ], 'start');

        $this->assertExpressionEquals($expected, $grammar['start']);
    }

    public function testItPreventsDoubleTracing()
    {
        $grammar = GrammarBuilder::create('test')
            ->rule('start')->oneOf()
                ->literal('foo')
                ->literal('bar')
            ->getGrammar()
            ->tracing()
            ->tracing();

        $expected = new Trace(
            new OneOf([
                new Trace(new Literal('foo')),
                new Trace(new Literal('bar')),
            ], 'start'),
            'start'
        );

        $this->assertExpressionEquals($expected, $grammar['start']);
    }
}
