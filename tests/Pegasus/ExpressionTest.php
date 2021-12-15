<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests;

use ju1ius\Pegasus\Expression;

class ExpressionTest extends PegasusTestCase
{
    public function testClone()
    {
        $expr = $this->getMockForAbstractClass(Expression::class);
        $clone = clone $expr;
        $this->assertNotSame($expr->id, $clone->id);
    }

    public function testWakeUp()
    {
        $expr = $this->getMockForAbstractClass(Expression::class);
        $new = unserialize(serialize($expr));
        $this->assertNotSame($expr->id, $new->id);
    }

    public function testIterate()
    {
        $expr = $this->getMockForAbstractClass(Expression::class);
        $a = iterator_to_array($expr->iterate());
        $this->assertSame(1, count($a));
        $this->assertSame($expr, $a[0]);
    }

    public function testDefaultMetadata()
    {
        $expr = $this->getMockForAbstractClass(Expression::class);
        $this->assertTrue($expr->isCapturing());
        $this->assertTrue($expr->isCapturingDecidable());
        $this->assertFalse($expr->hasVariableCaptureCount());
    }
}
