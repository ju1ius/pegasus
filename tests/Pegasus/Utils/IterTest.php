<?php
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Tests\Utils;

use ju1ius\Pegasus\Utils\Iter;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
class IterTest extends \PHPUnit_Framework_TestCase
{
    public function testMap()
    {
        $mock = $this->getMockBuilder('stdClass')
            ->setMethods(['callback'])
            ->getMock();
        $mock->expects($this->exactly(4))
            ->method('callback')
            ->willReturnArgument(0);

        $input = new \ArrayIterator([1, 2, 3, 4]);
        $result = iterator_to_array(Iter::map([$mock, 'callback'], $input));
        $this->assertEquals([1, 2, 3, 4], $result);
    }

    public function testEvery()
    {
        $input = new \ArrayIterator([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        $result = Iter::every(function ($value) {
            return $value > 0;
        }, $input);
        $this->assertTrue($result);

        $result = Iter::every(function ($value) {
            return $value > 5;
        }, $input);
        $this->assertFalse($result);
    }

    public function testSome()
    {
        $input = new \ArrayIterator([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        $result = Iter::some(function ($value) {
            return $value % 2 === 0;
        }, $input);
        $this->assertTrue($result);

        $result = Iter::some(function ($value) {
            return $value > 10;
        }, $input);
        $this->assertFalse($result);
    }

    public function testFind()
    {
        $input = new \ArrayIterator([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        $result = Iter::find(function ($value) {
            return $value % 3 === 0;
        }, $input);
        $this->assertSame(3, $result);

        $result = Iter::find(function ($value) {
            return $value === 42;
        }, $input);
        $this->assertNull($result);
    }

    public function testConsecutive()
    {
        $input = new \ArrayIterator([1, 2, 3, 4, 5, 6, 7, 8, 9]);
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

        $this->assertEmpty(iterator_to_array(Iter::consecutive(3, [1, 2])));
    }

    public function testSomeConsecutive()
    {
        $input = new \ArrayIterator([1, 2, 3, 4, 5, 6, 7, 8, 9]);

        $result = Iter::someConsecutive(function ($value, $i, $chunk) {
            return array_sum($chunk) === 12;
        }, 3, $input);
        $this->assertTrue($result);

        $result = Iter::someConsecutive(function ($value, $i, $chunk) {
            return $value > 42;
        }, 3, $input);
        $this->assertFalse($result);
    }
}
