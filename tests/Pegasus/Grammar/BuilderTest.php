<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests\Grammar;

use ju1ius\Pegasus\Expression\Application\Super;
use ju1ius\Pegasus\Expression\Combinator\Sequence;
use ju1ius\Pegasus\Expression\Decorator\Ignore;
use ju1ius\Pegasus\Expression\Decorator\Label;
use ju1ius\Pegasus\Expression\Decorator\OneOrMore;
use ju1ius\Pegasus\Expression\Terminal\Literal;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\GrammarBuilder;
use ju1ius\Pegasus\Tests\PegasusTestCase;

class BuilderTest extends PegasusTestCase
{
    public function testCreate()
    {
        $grammar = GrammarBuilder::create('foo')->getGrammar();
        $this->assertInstanceOf(Grammar::class, $grammar);
        $this->assertSame('foo', $grammar->getName());
    }

    public function testOf()
    {
        $grammar = new Grammar();
        $this->assertSame($grammar, GrammarBuilder::of($grammar)->getGrammar());
    }

    public function testSuperWithIdentifier()
    {
        $grammar = GrammarBuilder::create()->rule('test')->super('foo')->getGrammar();
        $this->assertExpressionEquals(new Super('foo', 'test'), $grammar['test']);
    }

    public function testSuperWithoutIdentifier()
    {
        $grammar = GrammarBuilder::create()->rule('test')->super()->getGrammar();
        $this->assertExpressionEquals(new Super('test', 'test'), $grammar['test']);
    }

    public function testItCanAddSeveralRules()
    {
        $result = GrammarBuilder::create()
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
     * @dataProvider provideTestBuildingComplexRules
     *
     * @param Grammar $grammar
     * @param array   $expected
     */
    public function testBuildingComplexRules(Grammar $grammar, array $expected)
    {
        $expected = Grammar::fromArray($expected);
        $this->assertGrammarEquals($expected, $grammar);
    }

    public function provideTestBuildingComplexRules()
    {
        yield 'Sequence with nested decorators' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->ignore()->label('a')->oneOrMore()->literal('foo')
                ->literal('bar')
                ->getGrammar(),
            ['test' => new Sequence([
                new Ignore(new Label('a', new OneOrMore(new Literal('foo')))),
                new Literal('bar')
            ], 'test')]
        ];
    }
}
