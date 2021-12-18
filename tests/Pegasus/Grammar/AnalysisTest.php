<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests\Grammar;

use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\Analysis;
use ju1ius\Pegasus\GrammarBuilder;
use ju1ius\Pegasus\Tests\PegasusTestCase;
use PHPUnit\Framework\Assert;

class AnalysisTest extends PegasusTestCase
{
    /**
     * @dataProvider provideTestCanModifyBindings
     */
    public function testCanModifyBindings(Grammar $grammar, bool $expected)
    {
        $analysis = new Analysis($grammar);
        Assert::assertSame($expected, $analysis->canModifyBindings('test'));
    }

    public function provideTestCanModifyBindings(): iterable
    {
        yield 'No binding' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->literal('foo')
                ->literal('bar')
                ->getGrammar(),
            false
        ];
        yield 'Top-level binding' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->bindTo('foo')->literal('foo')
                ->ignore()->match('\s*')
                ->backReference('foo')
                ->getGrammar(),
            true
        ];
        yield 'Skipped binding' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->ignore()->bindTo('foo')->literal('foo')
                ->backReference('foo')
                ->getGrammar(),
            true
        ];
    }

    /**
     * @dataProvider provideTestIsRecursive
     */
    public function testIsRecursive(Grammar $grammar, string $rule, bool $expected)
    {
        $analysis = new Analysis($grammar);
        Assert::assertSame($expected, $analysis->isRecursive($rule));
        Assert::assertSame(!$expected, $analysis->isRegular($rule));
    }

    public function provideTestIsRecursive(): iterable
    {
        yield 'Directly recursive rule' => [
            GrammarBuilder::create()->rule('a')->sequence()
                ->literal('a')
                ->ref('a')
                ->getGrammar(),
            'a',
            true
        ];
        yield 'Indirectly recursive rule' => [
            GrammarBuilder::create()
                ->rule('a')->sequence()
                    ->literal('a')
                    ->ref('b')
                ->rule('b')->sequence()
                    ->literal('b')
                    ->ref('c')
                ->rule('c')->sequence()
                    ->literal('c')
                    ->ref('a')
                ->getGrammar(),
            'a',
            true
        ];
        yield 'Non-recursive rule' => [
            GrammarBuilder::create()
                ->rule('a')->sequence()
                    ->literal('a')
                    ->ref('b')
                ->rule('b')->sequence()
                    ->literal('b')
                    ->ref('c')
                ->rule('c')->sequence()
                    ->literal('c')
                    ->ref('b')
                ->getGrammar(),
            'a',
            false
        ];
        yield 'A rule with a super call' => [
            GrammarBuilder::create()->rule('a')->sequence()
                ->literal('a')
                ->super('a')
                ->getGrammar(),
            'a',
            true
        ];
    }

    /**
     * @dataProvider provideTestLeftIsRecursive
     */
    public function testIsLeftRecursive(Grammar $grammar, string $rule, bool $expected)
    {
        $analysis = new Analysis($grammar);
        Assert::assertSame($expected, $analysis->isLeftRecursive($rule));
    }

    public function provideTestLeftIsRecursive(): iterable
    {
        return [
            'Directly left-recursive rule' => [
                GrammarBuilder::create()->rule('a')->oneOf()
                    ->ref('a')
                    ->literal('a')
                    ->getGrammar(),
                'a',
                true
            ],
            'Indirectly left-recursive rule' => [
                GrammarBuilder::create()
                    ->rule('a')->ref('b')
                    ->rule('b')->ref('c')
                    ->rule('c')->ref('a')
                    ->getGrammar(),
                'a',
                true
            ],
            'Non-recursive rule' => [
                GrammarBuilder::create()
                    ->rule('a')->ref('b')
                    ->rule('b')->ref('c')
                    ->rule('c')->ref('b')
                    ->getGrammar(),
                'a',
                false
            ],
            'Recursive but not left-recursive rule' => [
                GrammarBuilder::create()->rule('a')->sequence()
                    ->literal('a')
                    ->ref('a')
                    ->getGrammar(),
                'a',
                false
            ]
        ];
    }

    public function testIsReferenced()
    {
        $grammar = GrammarBuilder::create()
            ->rule('foobarbaz')->oneOf()
                ->ref('foobarbaz')
                ->ref('foobar')
                ->ref('baz')
            ->rule('foobar')->sequence()
                ->ref('foo')
                ->ref('bar')
            ->rule('foo')->literal('foo')
            ->rule('bar')->literal('bar')
            ->rule('baz')->literal('baz')
            ->rule('lonely')->literal('nope')
            ->getGrammar();
        $analysis = new Analysis($grammar);

        Assert::assertFalse($analysis->isReferenced('lonely'));
        Assert::assertTrue($analysis->isReferenced('bar'));
    }

    public function testGetReferencesFrom()
    {
        $grammar = GrammarBuilder::create()
            ->rule('foobarbaz')->oneOf()
                ->ref('foobarbaz')
                ->ref('foobar')
                ->ref('baz')
            ->rule('foobar')->sequence()
                ->ref('foo')
                ->ref('bar')
            ->rule('foo')->literal('foo')
            ->rule('bar')->literal('bar')
            ->rule('baz')->literal('baz')
            ->getGrammar();
        $analysis = new Analysis($grammar);

        $expected = ['foobarbaz', 'foobar', 'foo', 'bar', 'baz'];
        Assert::assertEquals($expected, $analysis->getReferencesFrom('foobarbaz'));

        $expected = ['foo', 'bar'];
        Assert::assertEquals($expected, $analysis->getReferencesFrom('foobar'));

        Assert::assertEquals([], $analysis->getReferencesFrom('foo'));
    }

    public function testGetLeftReferencesFrom()
    {
        $grammar = GrammarBuilder::create()
            ->rule('xs')->oneOf()
                ->sequence()
                    ->ref('xs')
                    ->ref('x')
                ->end()
                ->ref('x')
            ->rule('x')->literal('x')
            ->getGrammar();
        $analysis = new Analysis($grammar);

        $expected = ['xs', 'x'];
        Assert::assertEquals($expected, $analysis->getLeftReferencesFrom('xs'));
    }
}
