<?php declare(strict_types=1);
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Tests;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\Exception\MissingStartRule;
use ju1ius\Pegasus\Grammar\Exception\RuleNotFound;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
class GrammarTest extends PegasusTestCase
{
    public function testGetRules()
    {
        $first = $this->getMockForAbstractClass(Expression::class);
        $last = $this->getMockForAbstractClass(Expression::class);

        $g = new Grammar();
        $g['first'] = $first;
        $g['last'] = $last;

        $this->assertEquals([
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

        $this->assertSame('foo', $g->getStartRule());
        $this->assertSame($startExpr, $g->getStartExpression());

        $g->setStartRule('bar');

        $this->assertSame('bar', $g->getStartRule());
        $this->assertSame($otherExpr, $g->getStartExpression());

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
        $this->expectException(\InvalidArgumentException::class);
        $g = new Grammar();
        $g['foo'] = new \stdClass();
    }

    public function testItThrowsWhenRuleIsNotFound()
    {
        $g = new Grammar();
        $this->assertFalse(isset($g['foo']));
        $this->expectException(RuleNotFound::class);
        $rule = $g['foo'];
    }

    public function testSetParent()
    {
        $child = new Grammar();
        $parent = new Grammar();
        $child->extends($parent);
        $this->assertSame($parent, $child->getParent());
    }

    public function testItFallsBackToParentRule()
    {
        $child = new Grammar();
        $parent = new Grammar();
        $expr = $this->getMockForAbstractClass(Expression::class);
        $parent['foo'] = $expr;
        $child->extends($parent);

        $this->assertTrue(isset($child['foo']));
        $this->assertSame($expr, $child['foo']);
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

        $this->assertSame($childFoo, $child['foo']);
        $this->assertSame($parentFoo, $parent['foo']);
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

        $this->assertSame($parentFoo, $child->super('foo'));
    }

    public function testInline()
    {
        $g = new Grammar();
        $g->inline('foo');
        $this->assertTrue($g->isInlined('foo'));
        $this->assertFalse($g->isInlined('bar'));
    }

    public function testCountReturnsTheNumberOfRules()
    {
        $g = new Grammar();
        $g['first'] = $this->getMockForAbstractClass(Expression::class);
        $g['second'] = $this->getMockForAbstractClass(Expression::class);
        $g['last'] = $this->getMockForAbstractClass(Expression::class);
        unset($g['second']);

        $this->assertSame(2, count($g));
    }

    public function testShallowCopy()
    {
        $expr = $this->getMockForAbstractClass(Expression::class);
        $g1 = new Grammar();
        $g1->setName('Foo');
        $g1['first'] = $expr;

        $g2 = $g1->copy();
        $this->assertInstanceOf(Grammar::class, $g2);
        $this->assertNotSame($g1, $g2);
        $this->assertSame('Foo', $g2->getName());
        $this->assertSame($expr, $g2['first']);
    }

    public function testDeepCopy()
    {
        $expr = $this->getMockForAbstractClass(Expression::class);
        $g1 = new Grammar();
        $g1->setName('Foo');
        $g1['first'] = $expr;

        $g2 = $g1->copy(true);
        $this->assertInstanceOf(Grammar::class, $g2);
        $this->assertNotSame($g1, $g2);
        $this->assertSame('Foo', $g2->getName());
        $this->assertNotSame($expr, $g2['first']);
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

        $this->assertInstanceOf(Grammar::class, $g3);
        $this->assertNotSame($g1, $g3);
        $this->assertNotSame($g2, $g3);
        $this->assertSame('Foo', $g1->getName());

        $this->assertNotSame($g1['foo'], $g3['foo']);
        $this->assertSame('foo', $g3['foo']->getName());
        $this->assertNotSame($g2['bar'], $g3['bar']);
        $this->assertSame('bar', $g3['bar']->getName());
    }

    public function testFilter()
    {
        $g = new Grammar();
        $g->setName('Foo');
        $g['first'] = $this->getMockForAbstractClass(Expression::class);
        $g['second'] = $this->getMockForAbstractClass(Expression::class);
        $g['last'] = $this->getMockForAbstractClass(Expression::class);

        $g2 = $g->filter(function ($expr, $ruleName, $g) {
            $this->assertInstanceOf(Expression::class, $expr);
            $this->assertInstanceOf(Grammar::class, $g);

            return $ruleName !== 'second';
        });
        $this->assertInstanceOf(Grammar::class, $g2);
        $this->assertNotSame($g, $g2);
        $this->assertSame('Foo', $g2->getName());
        $this->assertSame(2, count($g2));
        $this->assertInstanceOf(Expression::class, $g2['first']);
        $this->assertInstanceOf(Expression::class, $g2['last']);
        $this->assertFalse(isset($g2['second']));
    }

    public function testMap()
    {
        $g = new Grammar();
        $g->setName('Foo');
        $g['first'] = $this->getMockForAbstractClass(Expression::class);
        $g['last'] = $this->getMockForAbstractClass(Expression::class);

        $g2 = $g->map(function ($expr, $ruleName, $grammar) {
            $this->assertInstanceOf(Expression::class, $expr);
            $this->assertInstanceOf(Grammar::class, $grammar);
            // stupid but at some point we have to test something...
            $expr->id = 666;

            return $expr;
        });
        $this->assertInstanceOf(Grammar::class, $g2);
        $this->assertNotSame($g, $g2);
        $this->assertSame('Foo', $g2->getName());
        $this->assertSame(2, count($g2));
        $this->assertInstanceOf(Expression::class, $g2['first']);
        $this->assertSame(666, $g2['first']->id);
        $this->assertInstanceOf(Expression::class, $g2['last']);
        $this->assertSame(666, $g2['last']->id);
    }
}
