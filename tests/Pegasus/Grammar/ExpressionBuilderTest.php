<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests\Grammar;

use ju1ius\Pegasus\Expression\Application\Reference;
use ju1ius\Pegasus\Expression\Application\Reference as Ref;
use ju1ius\Pegasus\Expression\Combinator\OneOf;
use ju1ius\Pegasus\Expression\Combinator\Sequence;
use ju1ius\Pegasus\Expression\Combinator\Sequence as Seq;
use ju1ius\Pegasus\Expression\Composite;
use ju1ius\Pegasus\Expression\Decorator;
use ju1ius\Pegasus\Expression\Decorator\Assert as AssertExpr;
use ju1ius\Pegasus\Expression\Decorator\Bind;
use ju1ius\Pegasus\Expression\Decorator\Ignore;
use ju1ius\Pegasus\Expression\Decorator\NodeAction;
use ju1ius\Pegasus\Expression\Decorator\Not;
use ju1ius\Pegasus\Expression\Decorator\Quantifier;
use ju1ius\Pegasus\Expression\Decorator\Token;
use ju1ius\Pegasus\Expression\Terminal\BackReference;
use ju1ius\Pegasus\Expression\Terminal\CapturingRegExp;
use ju1ius\Pegasus\Expression\Terminal\EOF;
use ju1ius\Pegasus\Expression\Terminal\Epsilon;
use ju1ius\Pegasus\Expression\Terminal\Fail;
use ju1ius\Pegasus\Expression\Terminal\Literal;
use ju1ius\Pegasus\Expression\Terminal\RegExp;
use ju1ius\Pegasus\Expression\TerminalExpression;
use ju1ius\Pegasus\ExpressionBuilder as Builder;
use ju1ius\Pegasus\Tests\PegasusAssert;
use ju1ius\Pegasus\Tests\PegasusTestCase;
use PHPUnit\Framework\Assert;

/**
 * @coversDefaultClass \ju1ius\Pegasus\ExpressionBuilder
 */
class ExpressionBuilderTest extends PegasusTestCase
{
    public function testAddReturnsBuilder()
    {
        $expr = $this->getMockForAbstractClass(TerminalExpression::class);
        $builder = Builder::create();
        Assert::assertSame($builder, $builder->add($expr));
    }

    public function testAddSingleTerminal()
    {
        $expr = $this->getMockForAbstractClass(TerminalExpression::class);
        $result = Builder::create()->add($expr)->getExpression();
        Assert::assertSame($expr, $result);
    }

    public function testTerminalCannotHaveChildren()
    {
        $term = $this->getMockForAbstractClass(TerminalExpression::class);
        $term2 = $this->getMockForAbstractClass(TerminalExpression::class);
        $this->expectException(\RuntimeException::class);

        Builder::create()
            ->add($term)
                ->add($term2)
            ->getExpression();
    }

    public function testAddingTwoTopLevelExpressions()
    {
        $comp = $this->getMockForAbstractClass(Composite::class);
        $term = $this->getMockForAbstractClass(TerminalExpression::class);
        $this->expectException(\RuntimeException::class);

        Builder::create()
            ->add($comp)->end()
            ->add($term)
            ->getExpression();
    }

    public function testGetExpressionEndsComposite()
    {
        $comp = $this->getMockForAbstractClass(Composite::class);

        $result = Builder::create()
            ->add($comp)
            ->getExpression();
        Assert::assertSame($comp, $result);
    }

    public function testAddChildToComposite()
    {
        $comp = $this->getMockForAbstractClass(Composite::class);
        $term = $this->getMockForAbstractClass(TerminalExpression::class);

        $result = Builder::create()
            ->add($comp)
                ->add($term)
            ->end()
            ->getExpression();
        Assert::assertSame($comp, $result);
        Assert::assertSame($comp[0], $term);
    }

    public function testDecoratorsHaveSingleChild()
    {
        $deco = $this->getMockForAbstractClass(Decorator::class);
        $term = $this->getMockForAbstractClass(TerminalExpression::class);
        $term2 = $this->getMockForAbstractClass(TerminalExpression::class);

        $this->expectException(\OverflowException::class);
        $result = Builder::create()
            ->add($deco)
                ->add($term)
                ->add($term2)
            ->getExpression();
    }

    public function testNestedDecorators()
    {
        $deco1 = $this->getMockForAbstractClass(Decorator::class);
        $deco2 = $this->getMockForAbstractClass(Decorator::class);
        $term1 = $this->getMockForAbstractClass(TerminalExpression::class);
        $term2 = $this->getMockForAbstractClass(TerminalExpression::class);

        $result = Builder::create()
            ->add($deco1)->add($deco2)->add($term1)
            //->add($term2)
            ->getExpression();
        Assert::assertSame($deco1[0], $deco2);
        Assert::assertSame($deco2[0], $term1);
    }

    public function testNestingDecoratorsAndComposites()
    {
        $topSeq = $this->getMockForAbstractClass(Composite::class);
        $innerSeq = $this->getMockForAbstractClass(Composite::class);
        $deco = $this->getMockForAbstractClass(Decorator::class);
        $term1 = $this->getMockForAbstractClass(TerminalExpression::class);
        $term2 = $this->getMockForAbstractClass(TerminalExpression::class);

        $result = Builder::create()->add($topSeq)
            ->add($deco)
                ->add($innerSeq)
                    ->add($term1)
                ->end()
            ->add($term2)
            ->getExpression();
        Assert::assertCount(2, $topSeq);
        Assert::assertSame($topSeq[0], $deco);
        Assert::assertSame($topSeq[1], $term2);
        Assert::assertCount(1, $deco);
        Assert::assertSame($deco[0], $innerSeq);
        Assert::assertCount(1, $innerSeq);
        Assert::assertSame($innerSeq[0], $term1);
    }

    public function testItCanBuildExpressions()
    {
        foreach ($this->provideTestItCanBuildExpressions() as $msg => [$input, $expected]) {
            PegasusAssert::expressionEquals($expected, $input, $msg);
        }
    }

    public function provideTestItCanBuildExpressions(): iterable
    {
        // Terminals
        yield 'Literal' => [
            Builder::create()->literal('foo')->getExpression(),
            new Literal('foo'),
        ];
        yield 'Match' => [
            Builder::create()->match('foo', ['i'])->getExpression(),
            new RegExp('foo', ['i']),
        ];
        yield 'RegExp' => [
            Builder::create()->regexp('foo(bar)', ['i'])->getExpression(),
            new CapturingRegExp('foo(bar)', ['i']),
        ];
        yield 'Reference' => [
            Builder::create()->ref('foo')->getExpression(),
            new Reference('foo'),
        ];
        yield 'BackReference' => [
            Builder::create()->backref('foo')->getExpression(),
            new BackReference('foo'),
        ];
        yield 'EOF' => [
            Builder::create()->eof()->getExpression(),
            new EOF(),
        ];
        yield 'Epsilon' => [
            Builder::create()->epsilon()->getExpression(),
            new Epsilon(),
        ];
        yield 'Fail' => [
            Builder::create()->fail()->getExpression(),
            new Fail(),
        ];
        // Predicates
        yield 'Assert' => [
            Builder::create()->assert()->literal('foo')->getExpression(),
            new AssertExpr(new Literal('foo')),
        ];
        yield 'Not' => [
            Builder::create()->not()->literal('foo')->getExpression(),
            new Not(new Literal('foo')),
        ];
        // Composites
        yield 'Sequence' => [
            new Seq([new Literal('foo'), new Ref('bar')]),
            Builder::create()->seq()
                ->literal('foo')
                ->ref('bar')
                ->getExpression(),
        ];
        yield 'NodeAction' => [
            new NodeAction(new Sequence([new Literal('foo'), new Literal('bar')]), 'FooBar'),
            Builder::create()->named('FooBar')->sequence()
                ->literal('foo')
                ->literal('bar')
                ->getExpression(),
        ];
        yield 'Choice' => [
            new OneOf([new Literal('bar'), new Literal('baz')]),
            Builder::create()->oneOf()
                ->literal('bar')
                ->literal('baz')
                ->getExpression(),
        ];
        yield 'Choice of sequences' => [
            Builder::create()->oneOf()
                ->seq()->literal('foo')->literal('bar')->end()
                ->seq()->ref('baz')->ref('qux')->end()
                ->getExpression(),
            new OneOf([
                new Seq([new Literal('foo'), new Literal('bar')]),
                new Seq([new Ref('baz'), new Ref('qux')]),
            ]),
        ];
        yield 'Sequence of choices' => [
            Builder::create()->seq()
                ->oneOf()->literal('foo')->literal('bar')->end()
                ->oneOf()->ref('baz')->ref('qux')->end()
                ->getExpression(),
            new Seq([
                new OneOf([new Literal('foo'), new Literal('bar')]),
                new OneOf([new Ref('baz'), new Ref('qux')]),
            ]),
        ];
        // Decorators
        yield 'Top-level decorator' => [
            Builder::create()->q(1)->literal('foo')->getExpression(),
            new Quantifier(new Literal('foo'), 1),
        ];
        yield 'Nested decorators' => [
            Builder::create()->not()->exactly(1)->literal('foo')->getExpression(),
            new Not(new Quantifier(new Literal('foo'), 1, 1)),
        ];
        yield 'Quantifiers' => [
            Builder::create()->seq()
                ->q(1)->literal('foo')
                ->exactly(1)->literal('bar')
                ->q(2, 42)->literal('baz')
                ->getExpression(),
            new Seq([
                new Quantifier(new Literal('foo'), 1),
                new Quantifier(new Literal('bar'), 1, 1),
                new Quantifier(new Literal('baz'), 2, 42),
            ]),
        ];
        yield 'Binding' => [
            Builder::create()->bindTo('a')->literal('foo')->getExpression(),
            new Bind('a', new Literal('foo')),
        ];
        yield 'Skip' => [
            Builder::create()->ignore()->literal('foo')->getExpression(),
            new Ignore(new Literal('foo')),
        ];
        yield 'Token' => [
            Builder::create()->asToken()->literal('foo')->getExpression(),
            new Token(new Literal('foo')),
        ];
    }
}
