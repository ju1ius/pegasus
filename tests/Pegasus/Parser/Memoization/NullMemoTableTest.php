<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests\Parser\Memoization;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Parser\Memoization\MemoEntry;
use ju1ius\Pegasus\Parser\Memoization\NullMemoTable;
use PHPUnit\Framework\TestCase;


/**
 * @coversDefaultClass \ju1ius\Pegasus\Parser\Memoization\NullMemoTable
 */
class NullMemoTableTest extends TestCase
{
    /**
     * @covers ::has
     */
    public function testHas()
    {
        $expr = $this->getMockForAbstractClass(Expression::class);
        $memo = new NullMemoTable();
        $this->assertFalse($memo->has(42, $expr));
    }

    /**
     * @covers ::get
     */
    public function testGet()
    {
        $expr = $this->getMockForAbstractClass(Expression::class);
        $memo = new NullMemoTable();
        $this->assertNull($memo->get(42, $expr));
    }

    /**
     * @covers ::set
     */
    public function testSet()
    {
        $expr = $this->getMockForAbstractClass(Expression::class);
        $memo = new NullMemoTable();
        $entry = $memo->set(42, $expr, 666);
        $this->assertInstanceOf(MemoEntry::class, $entry);
        $this->assertSame(42, $entry->end);
        $this->assertSame(666, $entry->result);
    }
}
