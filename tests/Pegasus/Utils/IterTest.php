<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests\Utils;

use ju1ius\Pegasus\Utils\Iter;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class IterTest extends TestCase
{
    public function testMap()
    {
        $count = 0;
        $map = static function($x) use(&$count) {
            $count++;
            return $x;
        };
        $input = Iter::of([1, 2, 3, 4]);
        $result = iterator_to_array(Iter::map($map, $input));
        Assert::assertSame(4, $count);
        $this->assertEquals([1, 2, 3, 4], $result);
    }

    public function testEvery()
    {
        $input = Iter::of([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        $result = Iter::every(fn($value) => $value > 0, $input);
        $this->assertTrue($result);

        $result = Iter::every(fn($value) => $value > 5, $input);
        $this->assertFalse($result);
    }

    public function testSome()
    {
        $input = Iter::of([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        $result = Iter::some(fn($value) => $value % 2 === 0, $input);
        $this->assertTrue($result);

        $result = Iter::some(fn($value) => $value > 10, $input);
        $this->assertFalse($result);
    }

    public function testFind()
    {
        $input = Iter::of([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        $result = Iter::find(fn($value) => $value % 3 === 0, $input);
        $this->assertSame(3, $result);

        $result = Iter::find(fn($value) => $value === 42, $input);
        $this->assertNull($result);
    }

    public function testConsecutive()
    {
        $input = Iter::of([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        $result = iterator_to_array(Iter::consecutive(3, $input));
        $expected = [
            [1, 2, 3],
            [2, 3, 4],
            [3, 4, 5],
            [4, 5, 6],
            [5, 6, 7],
            [6, 7, 8],
            [7, 8, 9],
        ];
        $this->assertEquals($expected, $result);

        $this->assertEmpty(iterator_to_array(Iter::consecutive(3, Iter::of([1, 2]))));
    }

    public function testSomeConsecutive()
    {
        $input = Iter::of([1, 2, 3, 4, 5, 6, 7, 8, 9]);

        $result = Iter::someConsecutive(fn($value, $i, $chunk) => array_sum($chunk) === 12, 3, $input);
        $this->assertTrue($result);

        $result = Iter::someConsecutive(fn($value, $i, $chunk) => $value > 42, 3, $input);
        $this->assertFalse($result);
    }
}
