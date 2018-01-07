<?php declare(strict_types=1);


namespace ju1ius\Pegasus\Source;


use ju1ius\Pegasus\Source\Exception\OffsetNotFound;
use ju1ius\Pegasus\Source\Exception\PositionNotFound;
use ju1ius\Pegasus\Utils\Str;


final class SourceInfo
{
    /**
     * @var string
     */
    private $source;

    /**
     * @var int
     */
    private $sourceLength;

    /**
     * @var int
     */
    private $tabSize = 4;

    /**
     * The line info array (0-based offsets)
     * [
     *   int $lineno => [int $startOffset, int $endOffset, int $length, string $line],
     *   ...
     * ]
     * @var array
     */
    private $lines;

    /**
     * @var int
     */
    private $numLines;

    public function __construct(string $text, int $tabSize = 4)
    {
        $this->source = $text;
        $this->sourceLength = strlen($this->source);
        $this->tabSize = 4;
    }

    /**
     * Lazily initialises the line info array.
     *
     * @return array
     */
    private function getLines(): array
    {
        if (!$this->lines) {
            $this->lines = [];
            $lines = preg_split('/\R/', $this->source, -1, PREG_SPLIT_OFFSET_CAPTURE);
            foreach ($lines as $i => [$line, $offset]) {
                $length = strlen($line);
                $this->lines[$i] = [$offset, $offset + $length, $length, $line];
            }
            $this->numLines = count($lines);
        }

        return $this->lines;
    }

    public function slice(int $start = 0, ?int $length = null): array
    {
        $lines = $this->getLines();
        $slice = array_slice($lines, $start, $length, true);
        foreach ($slice as $i => $line) {
            $slice[$i] = end($line);
        }

        return $slice;
    }

    /**
     * @param int $offset
     * @return int[]
     */
    public function positionFromOffset(int $offset)
    {
        $line = $this->lineFromOffset($offset);
        [$bol] = $this->lines[$line];
        $column = $offset - $bol;

        return [$line, $column];
    }

    public function offsetFromPosition(int $line, int $column): int
    {
        $lines = $this->getLines();
        if ($line < 0 || $line > $this->numLines) {
            throw new PositionNotFound($line, $column);
        }
        list($bol, $eol, $length) = $lines[$line];
        if ($column > $eol - $bol) {
            throw new PositionNotFound($line, $column);
        }
        return $bol + $column;
    }


    /**
     * @param int $pos Byte offset of the source position to highlight
     * @param int $numLines
     * @param int $maxCols
     * @return string
     */
    public function getExcerpt(int $pos, int $numLines = 2, int $maxCols = 80): string
    {
        [$line, $column] = $this->positionFromOffset($pos);
        $startLine = $line - $numLines + 1;
        $length = $line - $startLine + 1;
        $lines = $this->slice($startLine, $length);

        $firstLineNo = key($lines) + 1;
        $targetLine = array_pop($lines);

        // handle lines before
        foreach ($lines as $lineno => $line) {
            $lines[$lineno] = sprintf(
                '%4d│ %s',
                $lineno + 1,
                Str::truncate($line[0], $maxCols - 6)
            );
        }
        // handle target line
        $lines[] = sprintf(
            '%4d│ %s',
            $line + 1,
            Str::truncate($targetLine[0], $maxCols - 6, $column)
        );

        $text = sprintf("Line %d, column %d:\n", $line + 1, $column + 1);
        if ($firstLineNo > 1) {
            $text .= "   …│  …\n";
        }
        $text .= implode("\n", $lines);
        $text .= sprintf("\n────┴╌%s┘", str_repeat('╌', $column));

        return $text;
    }

    /**
     * Performs a binary search on the line info array to find the line of the given position.
     *
     * @param int $offset
     * @return int
     */
    private function lineFromOffset(int $offset): int
    {
        if ($offset < 0 || $offset > $this->sourceLength) {
            throw new OffsetNotFound($offset);
        }
        $offset = max(0, min($this->sourceLength, $offset));
        $lines = $this->getLines();
        $minLine = 0;
        $maxLine = $this->numLines;
        $i = (int)floor($maxLine / 2);

        while (true) {
            [$bol, $eol] = $lines[$i];
            if ($offset >= $bol && $offset <= $eol) {
                return $i;
            }
            if ($offset < $bol) {
                $maxLine = $i;
            } elseif ($offset > $bol) {
                $minLine = $i;
            }
            $i = $minLine + (int)floor(($maxLine - $minLine) / 2);
        }
    }
}