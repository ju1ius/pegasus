<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests\Parser\Memoization;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Parser\Memoization\MemoEntry;
use ju1ius\Pegasus\Parser\Memoization\PackratMemoTable;
use PHPUnit\Framework\TestCase;


/**
 * @coversDefaultClass ju1ius\Pegasus\Parser\Memoization\PackratMemoTable
 */
class PackratMemoTableTest extends TestCase
{
    /**
     * @covers ::set
     */
    public function testSet()
    {
        $expr = $this->getMockForAbstractClass(Expression::class);
        $memo = new PackratMemoTable();
        $pos = 42;
        $result = 666;
        $entry = $memo->set($pos, $expr, $result);
        $this->assertInstanceOf(MemoEntry::class, $entry);
        $this->assertSame($pos, $entry->end);
        $this->assertSame($result, $entry->result);

        return [$memo, $pos, $expr, $entry, $result];
    }

    /**
     * @depends testSet
     * @covers ::has
     */
    public function testHas(array $args)
    {
        [$memo, $pos, $expr] = $args;
        $this->assertTrue($memo->has($pos, $expr));
        $this->assertFalse($memo->has($pos - 1, $expr));
    }

    /**
     * @depends testSet
     * @covers ::get
     */
    public function testGet(array $args)
    {
        [$memo, $pos, $expr, $entry] = $args;

        $resultEntry = $memo->get($pos, $expr);
        $this->assertSame($entry, $resultEntry);
        $this->assertSame($entry->end, $resultEntry->end);
        $this->assertSame($entry->result, $resultEntry->result);

        $this->assertNull($memo->get($pos - 1, $expr));
    }

    /**
     * @depends testSet
     * @covers ::cut
     */
    public function testCut(array $args)
    {
        [$memo, $pos, $expr, $entry, $result] = $args;
        $memo->cut($pos);
        $this->assertTrue($memo->has($pos, $expr));
        $memo->cut($pos + 1);
        $this->assertFalse($memo->has($pos, $expr));
    }
}
