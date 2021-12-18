<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests;

use ju1ius\Pegasus\Expression;
use PHPUnit\Framework\Assert;

class ExpressionTest extends PegasusTestCase
{
    public function testClone()
    {
        $expr = $this->getMockForAbstractClass(Expression::class);
        $clone = clone $expr;
        Assert::assertNotSame($expr->id, $clone->id);
    }

    public function testWakeUp()
    {
        $expr = $this->getMockForAbstractClass(Expression::class);
        $new = unserialize(serialize($expr));
        Assert::assertNotSame($expr->id, $new->id);
    }

    public function testIterate()
    {
        $expr = $this->getMockForAbstractClass(Expression::class);
        $a = iterator_to_array($expr->iterate());
        Assert::assertSame(1, count($a));
        Assert::assertSame($expr, $a[0]);
    }

    public function testDefaultMetadata()
    {
        $expr = $this->getMockForAbstractClass(Expression::class);
        Assert::assertTrue($expr->isCapturing());
        Assert::assertTrue($expr->isCapturingDecidable());
        Assert::assertFalse($expr->hasVariableCaptureCount());
    }
}
