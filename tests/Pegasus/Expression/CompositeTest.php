<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests\Expression;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Composite;
use ju1ius\Pegasus\Expression\Exception\ChildNotFound;
use ju1ius\Pegasus\Expression\Exception\InvalidChildType;
use ju1ius\Pegasus\Tests\ExpressionTestCase;
use PHPUnit\Framework\Assert;

class CompositeTest extends ExpressionTestCase
{
    public function testCount()
    {
        $comp = $this->getMockForAbstractClass(Composite::class, [[
            $this->createMock(Expression::class),
            $this->createMock(Expression::class),
        ]]);
        Assert::assertSame(2, count($comp));
    }

    public function testOffsetExists()
    {
        $comp = $this->getMockForAbstractClass(Composite::class, [[
            $this->createMock(Expression::class),
        ]]);
        Assert::assertTrue(isset($comp[0]));
        Assert::assertFalse(isset($comp[1]));
    }

    public function testOffsetGet()
    {
        $child = $this->createMock(Expression::class);
        $comp = $this->getMockForAbstractClass(Composite::class, [[
            $child,
        ]]);
        Assert::assertSame($child, $comp[0]);

        $this->expectException(ChildNotFound::class);
        $foo = $comp[1];
    }

    public function testOffsetUnset()
    {
        $comp = $this->getMockForAbstractClass(Composite::class, [[
            $this->createMock(Expression::class),
        ]]);
        unset($comp[0]);

        Assert::assertFalse(isset($comp[0]));
    }

    public function testOffsetSet()
    {
        $comp = $this->getMockForAbstractClass(Composite::class);
        $child = $this->createMock(Expression::class);
        $child2 = $this->createMock(Expression::class);

        $comp[0] = $child;
        $comp[] = $child2;

        Assert::assertSame($child, $comp[0]);
        Assert::assertSame($child2, $comp[1]);

        $this->expectException(InvalidChildType::class);
        $comp[] = new \stdClass();
    }

    public function testIsCapturingReturnsTrueWhenOneChildrenIsCapturing()
    {
        $comp = $this->getMockForAbstractClass(Composite::class);

        $t1 = $this->createMock(Expression::class);
        $t1->method('isCapturing')->willReturn(true);
        $t2 = $this->createMock(Expression::class);
        $t2->method('isCapturing')->willReturn(false);

        $comp[0] = $t1;
        $comp[1] = $t2;

        Assert::assertTrue($comp->isCapturing());

        $t3 = $this->createMock(Expression::class);
        $t3->method('isCapturing')->willReturn(false);
        $comp[0] = $t3;

        Assert::assertFalse($comp->isCapturing());
    }

    public function testIsCapturingDecidableReturnsTrueIfAllChildrenAreDecidable()
    {
        $comp = $this->getMockForAbstractClass(Composite::class);

        $t1 = $this->createMock(Expression::class);
        $t1->method('isCapturingDecidable')->willReturn(true);
        $t2 = $this->createMock(Expression::class);
        $t2->method('isCapturingDecidable')->willReturn(false);

        $comp[0] = $t1;
        $comp[1] = $t2;

        Assert::assertFalse($comp->isCapturingDecidable());

        $t3 = $this->createMock(Expression::class);
        $t3->method('isCapturingDecidable')->willReturn(true);
        $comp[1] = $t3;

        Assert::assertTrue($comp->isCapturingDecidable());
    }

    public function testMap()
    {
        $comp = $this->getMockForAbstractClass(Composite::class, [[
            $this->createMock(Expression::class),
            $this->createMock(Expression::class),
        ]]);
        $newChild = $this->createMock(Expression::class);

        $newComp = $comp->map(function ($child, $i, $newComp) use ($comp, $newChild) {
            Assert::assertInstanceOf(Composite::class, $newComp);
            Assert::assertNotSame($comp, $newComp);
            return $newChild;
        });
        Assert::assertInstanceOf(Composite::class, $newComp);
        Assert::assertNotSame($comp, $newComp);
        Assert::assertSame($newChild, $newComp[0]);
        Assert::assertSame($newChild, $newComp[1]);
    }

    public function testIterate()
    {
        $child1 = $this->getMockForAbstractClass(Expression::class);
        $child2 = $this->getMockForAbstractClass(Expression::class);
        $comp = $this->getMockForAbstractClass(Composite::class, [[
            $child1,
            $child2,
        ]]);

        Assert::assertEquals([$comp, $child1, $child2], iterator_to_array($comp->iterate()));
        Assert::assertEquals([$child1, $child2, $comp], iterator_to_array($comp->iterate(true)));
    }
}
