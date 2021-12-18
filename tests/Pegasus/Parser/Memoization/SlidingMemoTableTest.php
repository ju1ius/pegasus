<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests\Parser\Memoization;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Parser\Memoization\MemoEntry;
use ju1ius\Pegasus\Parser\Memoization\SlidingMemoTable;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \ju1ius\Pegasus\Parser\Memoization\SlidingMemoTable
 */
class SlidingMemoTableTest extends TestCase
{
    public function testGetWindowSize()
    {
        $g = new Grammar();
        $g['one'] = $this->getMockForAbstractClass(Expression::class);
        $g['two'] = $this->getMockForAbstractClass(Expression::class);
        $memo = new SlidingMemoTable($g, 2);

        Assert::assertSame(2 * 2 + 1, $memo->getWindowSize());
    }

    public function testGetWindowSizeWithInheritance()
    {
        $parent = new Grammar();
        $parent['one'] = $this->getMockForAbstractClass(Expression::class);
        $child = new Grammar();
        $child['two'] = $this->getMockForAbstractClass(Expression::class);
        $child->extends($parent);
        $grandChild = new Grammar();
        $grandChild['three'] = $this->getMockForAbstractClass(Expression::class);
        $grandChild->extends($child);

        $memo = new SlidingMemoTable($grandChild, 2);

        Assert::assertSame(3 * 2 + 1, $memo->getWindowSize());
    }

    public function testGetWindowSizeWithComposition()
    {
        $grammar = new Grammar();
        $grammar['one'] = $this->getMockForAbstractClass(Expression::class);
        $g2 = new Grammar();
        $g2['two'] = $this->getMockForAbstractClass(Expression::class);
        $g3 = new Grammar();
        $g3['three'] = $this->getMockForAbstractClass(Expression::class);

        $g2->use($g3, 'g3');
        $grammar->use($g2, 'g2');

        $memo = new SlidingMemoTable($grammar, 2);

        Assert::assertSame(3 * 2 + 1, $memo->getWindowSize());
    }

    /**
     * @covers ::set
     */
    public function testSet()
    {
        $grammar = new Grammar();
        $expr = $this->getMockForAbstractClass(Expression::class);
        $grammar['one'] = $expr;

        // Make window size === 1, so second entry should overwrite the first
        $memo = new SlidingMemoTable($grammar, 0);
        $entry = $memo->set(0, $expr, 666);
        Assert::assertInstanceOf(MemoEntry::class, $entry);
        Assert::assertSame(0, $entry->end);
        Assert::assertSame(666, $entry->result);

        $entry = $memo->set(1, $expr, 999);
        Assert::assertInstanceOf(MemoEntry::class, $entry);
        Assert::assertSame(1, $entry->end);
        Assert::assertSame(999, $entry->result);

        return [$memo, $expr];
    }

    /**
     * @depends testSet
     * @covers ::has
     */
    public function testHas(array $args)
    {
        [$memo, $expr] = $args;
        Assert::assertFalse($memo->has(0, $expr));
        Assert::assertTrue($memo->has(1, $expr));
    }

    /**
     * @depends testSet
     * @covers ::get
     */
    public function testGet(array $args)
    {
        [$memo, $expr] = $args;
        Assert::assertNull($memo->get(0, $expr));

        $entry = $memo->get(1, $expr);
        Assert::assertSame(1, $entry->end);
        Assert::assertSame(999, $entry->result);
    }

    /**
     * @covers ::cut
     */
    public function testCut()
    {
        $grammar = new Grammar();
        $expr = $this->getMockForAbstractClass(Expression::class);
        $grammar['one'] = $expr;

        // Make window size === 1, so second entry should overwrite the first
        $memo = new SlidingMemoTable($grammar);
        $memo->set(21, $expr, 666);
        $memo->set(42, $expr, 999);
        $memo->cut(32);
        Assert::assertFalse($memo->has(21, $expr));
        Assert::assertTrue($memo->has(42, $expr));
    }
}
