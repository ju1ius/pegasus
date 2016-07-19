<?php
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Tests\Grammar;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Assert;
use ju1ius\Pegasus\Expression\BackReference;
use ju1ius\Pegasus\Expression\Composite;
use ju1ius\Pegasus\Expression\Decorator;
use ju1ius\Pegasus\Expression\EOF;
use ju1ius\Pegasus\Expression\Epsilon;
use ju1ius\Pegasus\Expression\Fail;
use ju1ius\Pegasus\Expression\Label;
use ju1ius\Pegasus\Expression\Literal;
use ju1ius\Pegasus\Expression\Match;
use ju1ius\Pegasus\Expression\NamedSequence;
use ju1ius\Pegasus\Expression\Not;
use ju1ius\Pegasus\Expression\OneOf;
use ju1ius\Pegasus\Expression\Quantifier;
use ju1ius\Pegasus\Expression\Reference;
use ju1ius\Pegasus\Expression\Reference as Ref;
use ju1ius\Pegasus\Expression\RegExp;
use ju1ius\Pegasus\Expression\Sequence as Seq;
use ju1ius\Pegasus\Expression\Skip;
use ju1ius\Pegasus\Expression\Terminal;
use ju1ius\Pegasus\Expression\Token;
use ju1ius\Pegasus\Grammar\ExpressionBuilder as Builder;
use ju1ius\Pegasus\Tests\PegasusTestCase;

/**
 * @coversDefaultClass \ju1ius\Pegasus\Grammar\ExpressionBuilder
 *
 * @author ju1ius <ju1ius@laposte.net>
 */
class ExpressionBuilderTest extends PegasusTestCase
{
    public function testAddReturnsBuilder()
    {
        $expr = $this->getMockForAbstractClass(Terminal::class);
        $builder = Builder::create();
        $this->assertSame($builder, $builder->add($expr));
    }

    public function testAddSingleTerminal()
    {
        $expr = $this->getMockForAbstractClass(Terminal::class);
        $result = Builder::create()->add($expr)->getExpression();
        $this->assertSame($expr, $result);
    }

    public function testTerminalCannotHaveChildren()
    {
        $term = $this->getMockForAbstractClass(Terminal::class);
        $term2 = $this->getMockForAbstractClass(Terminal::class);
        $this->expectException(\RuntimeException::class);

        Builder::create()
            ->add($term)
                ->add($term2)
            ->getExpression();
    }

    public function testAddingTwoTopLevelExpressions()
    {
        $comp = $this->getMockForAbstractClass(Composite::class);
        $term = $this->getMockForAbstractClass(Terminal::class);
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
        $this->assertSame($comp, $result);
    }

    public function testAddChildToComposite()
    {
        $comp = $this->getMockForAbstractClass(Composite::class);
        $term = $this->getMockForAbstractClass(Terminal::class);

        $result = Builder::create()
            ->add($comp)
                ->add($term)
            ->end()
            ->getExpression();
        $this->assertSame($comp, $result);
        $this->assertSame($comp[0], $term);
    }

    public function testDecoratorsHaveSingleChild()
    {
        $deco = $this->getMockForAbstractClass(Decorator::class);
        $term = $this->getMockForAbstractClass(Terminal::class);
        $term2 = $this->getMockForAbstractClass(Terminal::class);

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
        $term1 = $this->getMockForAbstractClass(Terminal::class);
        $term2 = $this->getMockForAbstractClass(Terminal::class);

        $result = Builder::create()
            ->add($deco1)->add($deco2)->add($term1)
            //->add($term2)
            ->getExpression();
        $this->assertSame($deco1[0], $deco2);
        $this->assertSame($deco2[0], $term1);
    }

    public function testItCanBuildExpressions()
    {
        foreach ($this->getItCanBuildExpressionsProvider() as $msg => list($input, $expected)) {
            $this->assertExpressionEquals($expected, $input, $msg);
        }
    }

    public function getItCanBuildExpressionsProvider()
    {
        return [
            // Terminals
            'Literal' => [
                Builder::create()->literal('foo')->getExpression(),
                new Literal('foo'),
            ],
            'Match' => [
                Builder::create()->match('foo', ['i'])->getExpression(),
                new Match('foo', ['i']),
            ],
            'RegExp' => [
                Builder::create()->regexp('foo(bar)', ['i'])->getExpression(),
                new RegExp('foo(bar)', ['i']),
            ],
            'Reference' => [
                Builder::create()->ref('foo')->getExpression(),
                new Reference('foo'),
            ],
            'BackReference' => [
                Builder::create()->backref('foo')->getExpression(),
                new BackReference('foo'),
            ],
            'EOF' => [
                Builder::create()->eof()->getExpression(),
                new EOF(),
            ],
            'Epsilon' => [
                Builder::create()->epsilon()->getExpression(),
                new Epsilon(),
            ],
            'Fail' => [
                Builder::create()->fail()->getExpression(),
                new Fail(),
            ],
            // Predicates
            'Assert' => [
                Builder::create()->assert()->literal('foo')->getExpression(),
                new Assert(new Literal('foo')),
            ],
            'Not' => [
                Builder::create()->not()->literal('foo')->getExpression(),
                new Not(new Literal('foo')),
            ],
            // Composites
            'Sequence' => [
                new Seq([new Literal('foo'), new Ref('bar')]),
                Builder::create()->seq()
                    ->literal('foo')
                    ->ref('bar')
                    ->getExpression(),
            ],
            'Named Sequence' => [
                new NamedSequence([new Literal('foo'), new Literal('bar')], 'FooBar'),
                Builder::create()->named('FooBar')
                    ->literal('foo')
                    ->literal('bar')
                    ->getExpression(),
            ],
            'Choice' => [
                new OneOf([new Literal('bar'), new Literal('baz')]),
                Builder::create()->oneOf()
                    ->literal('bar')
                    ->literal('baz')
                    ->getExpression(),
            ],
            'Choice of sequences' => [
                Builder::create()->oneOf()
                    ->seq()->literal('foo')->literal('bar')->end()
                    ->seq()->ref('baz')->ref('qux')->end()
                    ->getExpression(),
                new OneOf([
                    new Seq([new Literal('foo'), new Literal('bar')]),
                    new Seq([new Ref('baz'), new Ref('qux')]),
                ]),
            ],
            'Sequence of choices' => [
                Builder::create()->seq()
                    ->oneOf()->literal('foo')->literal('bar')->end()
                    ->oneOf()->ref('baz')->ref('qux')->end()
                    ->getExpression(),
                new Seq([
                    new OneOf([new Literal('foo'), new Literal('bar')]),
                    new OneOf([new Ref('baz'), new Ref('qux')]),
                ]),
            ],
            // Decorators
            'Top-level decorator' => [
                Builder::create()->q(1)->literal('foo')->getExpression(),
                new Quantifier(new Literal('foo'), 1, INF),
            ],
            'Nested decorators' => [
                Builder::create()->not()->exactly(1)->literal('foo')->getExpression(),
                new Not(new Quantifier(new Literal('foo'), 1, 1)),
            ],
            'Quantifiers' => [
                Builder::create()->seq()
                    ->q(1)->literal('foo')
                    ->exactly(1)->literal('bar')
                    ->q(2, 42)->literal('baz')
                    ->getExpression(),
                new Seq([
                    new Quantifier(new Literal('foo'), 1, INF),
                    new Quantifier(new Literal('bar'), 1, 1),
                    new Quantifier(new Literal('baz'), 2, 42),
                ]),
            ],
            'Label' => [
                Builder::create()->label('a')->literal('foo')->getExpression(),
                new Label(new Literal('foo'), 'a'),
            ],
            'Skip' => [
                Builder::create()->skip()->literal('foo')->getExpression(),
                new Skip(new Literal('foo')),
            ],
            'Token' => [
                Builder::create()->token()->literal('foo')->getExpression(),
                new Token(new Literal('foo')),
            ]
        ];
    }
}
