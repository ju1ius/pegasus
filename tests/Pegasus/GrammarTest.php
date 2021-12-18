<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\Exception\InvalidRuleType;
use ju1ius\Pegasus\Grammar\Exception\MissingStartRule;
use ju1ius\Pegasus\Grammar\Exception\RuleNotFound;
use PHPUnit\Framework\Assert;

class GrammarTest extends PegasusTestCase
{
    public function testGetRules()
    {
        $first = $this->getMockForAbstractClass(Expression::class);
        $last = $this->getMockForAbstractClass(Expression::class);

        $g = new Grammar();
        $g['first'] = $first;
        $g['last'] = $last;

        Assert::assertEquals([
            'first' => $first,
            'last' => $last,
        ], $g->getRules());
    }

    public function testStartRule()
    {
        $startExpr = $this->getMockForAbstractClass(Expression::class);
        $otherExpr = $this->getMockForAbstractClass(Expression::class);
        $g = new Grammar();
        $g['foo'] = $startExpr;
        $g['bar'] = $otherExpr;

        Assert::assertSame('foo', $g->getStartRule());
        Assert::assertSame($startExpr, $g->getStartExpression());

        $g->setStartRule('bar');

        Assert::assertSame('bar', $g->getStartRule());
        Assert::assertSame($otherExpr, $g->getStartExpression());

        $this->expectException(RuleNotFound::class);
        $g->setStartRule('404NotFound');
    }

    public function testMissingStartRule()
    {
        $g = new Grammar();
        $this->expectException(MissingStartRule::class);
        $nope = $g->getStartRule();
    }

    public function testItOnlyAcceptsExpressions()
    {
        $this->expectException(InvalidRuleType::class);
        $g = new Grammar();
        $g['foo'] = new \stdClass();
    }

    public function testItThrowsWhenRuleIsNotFound()
    {
        $g = new Grammar();
        Assert::assertFalse(isset($g['foo']));
        $this->expectException(RuleNotFound::class);
        $rule = $g['foo'];
    }

    public function testSetParent()
    {
        $child = new Grammar();
        $parent = new Grammar();
        $child->extends($parent);
        Assert::assertSame($parent, $child->getParent());
    }

    public function testItFallsBackToParentRule()
    {
        $child = new Grammar();
        $parent = new Grammar();
        $expr = $this->getMockForAbstractClass(Expression::class);
        $parent['foo'] = $expr;
        $child->extends($parent);

        Assert::assertTrue(isset($child['foo']));
        Assert::assertSame($expr, $child['foo']);
    }

    public function testChildRulesOverrideParent()
    {
        $parent = new Grammar();
        $parentFoo = $this->getMockForAbstractClass(Expression::class);
        $parent['foo'] = $parentFoo;

        $child = new Grammar();
        $childFoo = $this->getMockForAbstractClass(Expression::class);
        $child['foo'] = $childFoo;

        $child->extends($parent);

        Assert::assertSame($childFoo, $child['foo']);
        Assert::assertSame($parentFoo, $parent['foo']);
    }

    public function testSuper()
    {
        $parent = new Grammar();
        $parentFoo = $this->getMockForAbstractClass(Expression::class);
        $parent['foo'] = $parentFoo;

        $child = new Grammar();
        $childFoo = $this->getMockForAbstractClass(Expression::class);
        $child['foo'] = $childFoo;

        $child->extends($parent);

        Assert::assertSame($parentFoo, $child->super('foo'));
    }

    public function testInline()
    {
        $g = new Grammar();
        $g->inline('foo');
        Assert::assertTrue($g->isInlined('foo'));
        Assert::assertFalse($g->isInlined('bar'));
    }

    public function testCountReturnsTheNumberOfRules()
    {
        $g = new Grammar();
        $g['first'] = $this->getMockForAbstractClass(Expression::class);
        $g['second'] = $this->getMockForAbstractClass(Expression::class);
        $g['last'] = $this->getMockForAbstractClass(Expression::class);
        unset($g['second']);

        Assert::assertSame(2, count($g));
    }

    public function testShallowCopy()
    {
        $expr = $this->getMockForAbstractClass(Expression::class);
        $g1 = new Grammar();
        $g1->setName('Foo');
        $g1['first'] = $expr;

        $g2 = $g1->copy();
        Assert::assertInstanceOf(Grammar::class, $g2);
        Assert::assertNotSame($g1, $g2);
        Assert::assertSame('Foo', $g2->getName());
        Assert::assertSame($expr, $g2['first']);
    }

    public function testDeepCopy()
    {
        $expr = $this->getMockForAbstractClass(Expression::class);
        $g1 = new Grammar();
        $g1->setName('Foo');
        $g1['first'] = $expr;

        $g2 = $g1->copy(true);
        Assert::assertInstanceOf(Grammar::class, $g2);
        Assert::assertNotSame($g1, $g2);
        Assert::assertSame('Foo', $g2->getName());
        Assert::assertNotSame($expr, $g2['first']);
    }

    public function testMerge()
    {
        $g1 = new Grammar();
        $g1->setName('Foo');
        $g1['foo'] = $this->getMockForAbstractClass(Expression::class);

        $g2 = new Grammar();
        $g2->setName('Bar');
        $g2['bar'] = $this->getMockForAbstractClass(Expression::class);

        $g3 = $g1->merge($g2);

        Assert::assertInstanceOf(Grammar::class, $g3);
        Assert::assertNotSame($g1, $g3);
        Assert::assertNotSame($g2, $g3);
        Assert::assertSame('Foo', $g1->getName());

        Assert::assertNotSame($g1['foo'], $g3['foo']);
        Assert::assertSame('foo', $g3['foo']->getName());
        Assert::assertNotSame($g2['bar'], $g3['bar']);
        Assert::assertSame('bar', $g3['bar']->getName());
    }

    public function testFilter()
    {
        $g = new Grammar();
        $g->setName('Foo');
        $g['first'] = $this->getMockForAbstractClass(Expression::class);
        $g['second'] = $this->getMockForAbstractClass(Expression::class);
        $g['last'] = $this->getMockForAbstractClass(Expression::class);

        $g2 = $g->filter(function ($expr, $ruleName, $g) {
            Assert::assertInstanceOf(Expression::class, $expr);
            Assert::assertInstanceOf(Grammar::class, $g);

            return $ruleName !== 'second';
        });
        Assert::assertInstanceOf(Grammar::class, $g2);
        Assert::assertNotSame($g, $g2);
        Assert::assertSame('Foo', $g2->getName());
        Assert::assertSame(2, count($g2));
        Assert::assertInstanceOf(Expression::class, $g2['first']);
        Assert::assertInstanceOf(Expression::class, $g2['last']);
        Assert::assertFalse(isset($g2['second']));
    }

    public function testMap()
    {
        $g = new Grammar();
        $g->setName('Foo');
        $g['first'] = $this->getMockForAbstractClass(Expression::class);
        $g['last'] = $this->getMockForAbstractClass(Expression::class);

        $g2 = $g->map(function ($expr, $ruleName, $grammar) {
            Assert::assertInstanceOf(Expression::class, $expr);
            Assert::assertInstanceOf(Grammar::class, $grammar);
            // stupid but at some point we have to test something...
            $expr->id = 666;

            return $expr;
        });
        Assert::assertInstanceOf(Grammar::class, $g2);
        Assert::assertNotSame($g, $g2);
        Assert::assertSame('Foo', $g2->getName());
        Assert::assertSame(2, count($g2));
        Assert::assertInstanceOf(Expression::class, $g2['first']);
        Assert::assertSame(666, $g2['first']->id);
        Assert::assertInstanceOf(Expression::class, $g2['last']);
        Assert::assertSame(666, $g2['last']->id);
    }
}
