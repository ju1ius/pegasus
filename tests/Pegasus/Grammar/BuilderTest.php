<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests\Grammar;

use ju1ius\Pegasus\Expression\Application\Super;
use ju1ius\Pegasus\Expression\Combinator\Sequence;
use ju1ius\Pegasus\Expression\Decorator\Bind;
use ju1ius\Pegasus\Expression\Decorator\Ignore;
use ju1ius\Pegasus\Expression\Decorator\OneOrMore;
use ju1ius\Pegasus\Expression\Terminal\Literal;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\GrammarBuilder;
use ju1ius\Pegasus\GrammarFactory;
use ju1ius\Pegasus\Tests\PegasusTestCase;
use ju1ius\Pegasus\Tests\PegasusAssert;
use PHPUnit\Framework\Assert;

class BuilderTest extends PegasusTestCase
{
    public function testCreate()
    {
        $grammar = GrammarBuilder::create('foo')->getGrammar();
        Assert::assertInstanceOf(Grammar::class, $grammar);
        Assert::assertSame('foo', $grammar->getName());
    }

    public function testOf()
    {
        $grammar = new Grammar();
        Assert::assertSame($grammar, GrammarBuilder::of($grammar)->getGrammar());
    }

    public function testSuperWithIdentifier()
    {
        $grammar = GrammarBuilder::create()->rule('test')->super('foo')->getGrammar();
        PegasusAssert::expressionEquals(new Super('foo', 'test'), $grammar['test']);
    }

    public function testSuperWithoutIdentifier()
    {
        $grammar = GrammarBuilder::create()->rule('test')->super()->getGrammar();
        PegasusAssert::expressionEquals(new Super('test', 'test'), $grammar['test']);
    }

    public function testItCanAddSeveralRules()
    {
        $result = GrammarBuilder::create()
            ->rule('foo')->literal('foo')
            ->rule('bar')->literal('bar')
            ->getGrammar();
        $expected = GrammarFactory::fromArray([
            'foo' => new Literal('foo'),
            'bar' => new Literal('bar')
        ]);
        PegasusAssert::grammarEquals($expected, $result);
    }

    /**
     * @dataProvider provideTestBuildingComplexRules
     */
    public function testBuildingComplexRules(Grammar $grammar, array $expected)
    {
        $expected = GrammarFactory::fromArray($expected);
        PegasusAssert::grammarEquals($expected, $grammar);
    }

    public function provideTestBuildingComplexRules(): \Traversable
    {
        yield 'Sequence with nested decorators' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->ignore()->bindTo('a')->oneOrMore()->literal('foo')
                ->literal('bar')
                ->getGrammar(),
            ['test' => new Sequence([
                new Ignore(new Bind('a', new OneOrMore(new Literal('foo')))),
                new Literal('bar')
            ], 'test')]
        ];
    }
}
