<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests\Parser\Memoization;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Parser\Memoization\MemoEntry;
use ju1ius\Pegasus\Parser\Memoization\PackratMemoTable;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \ju1ius\Pegasus\Parser\Memoization\PackratMemoTable
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
        Assert::assertInstanceOf(MemoEntry::class, $entry);
        Assert::assertSame($pos, $entry->end);
        Assert::assertSame($result, $entry->result);

        return [$memo, $pos, $expr, $entry, $result];
    }

    /**
     * @depends testSet
     * @covers ::has
     */
    public function testHas(array $args)
    {
        [$memo, $pos, $expr] = $args;
        Assert::assertTrue($memo->has($pos, $expr));
        Assert::assertFalse($memo->has($pos - 1, $expr));
    }

    /**
     * @depends testSet
     * @covers ::get
     */
    public function testGet(array $args)
    {
        [$memo, $pos, $expr, $entry] = $args;

        $resultEntry = $memo->get($pos, $expr);
        Assert::assertSame($entry, $resultEntry);
        Assert::assertSame($entry->end, $resultEntry->end);
        Assert::assertSame($entry->result, $resultEntry->result);

        Assert::assertNull($memo->get($pos - 1, $expr));
    }

    /**
     * @depends testSet
     * @covers ::cut
     */
    public function testCut(array $args)
    {
        [$memo, $pos, $expr, $entry, $result] = $args;
        $memo->cut($pos);
        Assert::assertTrue($memo->has($pos, $expr));
        $memo->cut($pos + 1);
        Assert::assertFalse($memo->has($pos, $expr));
    }
}
