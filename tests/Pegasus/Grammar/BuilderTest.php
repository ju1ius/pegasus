<?php

namespace ju1ius\Pegasus\Tests\Grammar;

use ju1ius\Pegasus\Expression\Label;
use ju1ius\Pegasus\Expression\Literal;
use ju1ius\Pegasus\Expression\Match;
use ju1ius\Pegasus\Expression\OneOrMore;
use ju1ius\Pegasus\Expression\Sequence;
use ju1ius\Pegasus\Expression\Skip;
use ju1ius\Pegasus\Expression\Super;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\Builder;
use ju1ius\Pegasus\Tests\PegasusTestCase;

class BuilderTest extends PegasusTestCase
{
    public function testCreate()
    {
        $grammar = Builder::create('foo')->getGrammar();
        $this->assertInstanceOf(Grammar::class, $grammar);
        $this->assertSame('foo', $grammar->getName());
    }

    public function testOf()
    {
        $grammar = new Grammar();
        $this->assertSame($grammar, Builder::of($grammar)->getGrammar());
    }

    public function testSuperWithIdentifier()
    {
        $grammar = Builder::create()->rule('test')->super('foo')->getGrammar();
        $this->assertExpressionEquals(new Super('foo', 'test'), $grammar['test']);
    }

    public function testSuperWithoutIdentifier()
    {
        $grammar = Builder::create()->rule('test')->super()->getGrammar();
        $this->assertExpressionEquals(new Super('test', 'test'), $grammar['test']);
    }

    public function testItCanAddSeveralRules()
    {
        $result = Builder::create()
            ->rule('foo')->literal('foo')
            ->rule('bar')->literal('bar')
            ->getGrammar();
        $expected = Grammar::fromArray([
            'foo' => new Literal('foo'),
            'bar' => new Literal('bar')
        ]);
        $this->assertGrammarEquals($expected, $result);
    }

    /**
     * @dataProvider getTestBuildingComplexRulesProvider
     *
     * @param Grammar $grammar
     * @param array   $expected
     */
    public function testBuildingComplexRules(Grammar $grammar, array $expected)
    {
        $expected = Grammar::fromArray($expected);
        $this->assertGrammarEquals($expected, $grammar);
    }

    public function getTestBuildingComplexRulesProvider()
    {
        return [
            'Sequence with nested decorators' => [
                Builder::create()->rule('test')->sequence()
                    ->skip()->label('a')->oneOrMore()->literal('foo')
                    ->literal('bar')
                    ->getGrammar(),
                ['test' => new Sequence([
                    new Skip(new Label(new OneOrMore(new Literal('foo')), 'a')),
                    new Literal('bar')
                ], 'test')]
            ]
        ];
    }
}
