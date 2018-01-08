<?php

namespace ju1ius\Pegasus\Tests\Source;


use ju1ius\Pegasus\Source\Exception\OffsetNotFound;
use ju1ius\Pegasus\Source\Exception\PositionNotFound;
use ju1ius\Pegasus\Source\SourceInfo;
use PHPUnit\Framework\TestCase;


class SourceInfoTest extends TestCase
{
    public function testOffsetNotFound()
    {
        $this->expectException(OffsetNotFound::class);
        $map = new SourceInfo('foo');
        $map->positionFromOffset(42);
    }

    public function testPositionNotFound()
    {
        $this->expectException(PositionNotFound::class);
        $map = new SourceInfo('foo');
        $map->offsetFromPosition(2, 42);
    }

    /**
     * @dataProvider positionFromOffsetProvider
     * @param string $source
     * @param int $offset
     * @param array $expected
     */
    public function testPositionFromOffset(string $source, int $offset, array $expected)
    {
        $map = new SourceInfo($source);
        $this->assertSame($expected, $map->positionFromOffset($offset));
    }

    public function positionFromOffsetProvider()
    {
        return [
            ["foo\nbar", 0, [0, 0]],
            ["foo\nbar", 3, [0, 3]],
            ["foo\nbar", 4, [1, 0]],
            ["foo\nbar", 6, [1, 2]],
            ["foo\nbar\rbaz", 8, [2, 0]],
            ["foo\nbar\r\nbaz", 9, [2, 0]],
        ];
    }

    /**
     * @dataProvider offsetFromPositionProvider
     * @param string $source
     * @param int[] $pos
     * @param int $offset
     */
    public function testOffsetFromPosition(string $source, array $pos, int $offset)
    {
        $map = new SourceInfo($source);
        [$line, $col] = $pos;
        $this->assertSame($offset, $map->offsetFromPosition($line, $col));
    }

    public function offsetFromPositionProvider()
    {
        return [
            ["foo\nbar", [0, 0], 0],
            ["foo\nbar", [0, 3], 3],
        ];
    }

    public function testExcerptWithShortLines()
    {
        $source = "123\n456\n789\nABC\nDEF";
        $info = new SourceInfo($source);
        $result = $info->getExcerpt(strpos($source, '1'));
        $expected = <<<'EOS'
Line 1, column 1:
1│ 123
─┴╌┘
EOS;
        $this->assertSame($expected, $result);

        $result = $info->getExcerpt(strpos($source, 'C'));
        $expected = <<<'EOS'
Line 4, column 3:
…│ …
3│ 789
4│ ABC
─┴╌╌╌┘
EOS;
        $this->assertSame($expected, $result);
    }

    public function testExcerptWithLongLines()
    {
        $source = "123456789\nABCDEF123";
        $info = new SourceInfo($source);
        $maxCols = 11;
        $result = $info->getExcerpt(strpos($source, 'E'), 1, 0, $maxCols);
        $expected = <<<'EOS'
Line 2, column 5:
1│ 123456 …
2│ ABCDEF …
─┴╌╌╌╌╌┘
EOS;
        $this->assertSame($expected, $result);

        $result = $info->getExcerpt(strlen($source) - 1, 1, 0, $maxCols);
        $expected = <<<'EOS'
Line 2, column 9:
1│ 123456 …
2│ … CDEF123
─┴╌╌╌╌╌╌╌╌╌┘
EOS;
        $this->assertSame($expected, $result);
    }
}
