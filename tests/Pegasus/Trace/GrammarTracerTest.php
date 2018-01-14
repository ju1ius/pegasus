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
            ->getGrammar();
        $grammar->tracing();

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
            ->getGrammar();
        $grammar->tracing(true)->tracing(false);

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
            ->getGrammar();
        $grammar->tracing()->tracing();

        $expected = new Trace(
            new OneOf([
                new Trace(new Literal('foo')),
                new Trace(new Literal('bar')),
            ], 'start'),
            'start'
        );

        $this->assertExpressionEquals($expected, $grammar['start']);
    }

    public function testItTracesParents()
    {
        $parent = GrammarBuilder::create('parent')
            ->rule('parent')->literal('foo')
            ->getGrammar();
        $child = GrammarBuilder::create('child')
            ->rule('child')->literal('bar')
            ->getGrammar();
        $child->extends($parent);
        $grandChild = GrammarBuilder::create('grandChild')
            ->rule('grandChild')->literal('baz')
            ->getGrammar();
        $grandChild->extends($child);
        $grandChild->tracing();

        $expected = new Trace(new Literal('foo', 'parent'), 'parent');

        $this->assertExpressionEquals($expected, $parent['parent']);
    }

    public function testItTracesTraits()
    {
        $grammar = GrammarBuilder::create('main')
            ->rule('main')->literal('foo')
            ->getGrammar();
        $g2 = GrammarBuilder::create('g2')
            ->rule('g2')->literal('bar')
            ->getGrammar();
        $g3 = GrammarBuilder::create('g3')
            ->rule('g3')->literal('baz')
            ->getGrammar();
        $grammar->use($g2);
        $g2->use($g3);
        $grammar->tracing();

        $expected = new Trace(new Literal('baz', 'g3'), 'g3');

        $this->assertExpressionEquals($expected, $g3['g3']);
    }
}
