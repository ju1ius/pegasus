<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Source;

use ju1ius\Pegasus\Source\Exception\OffsetNotFound;
use ju1ius\Pegasus\Source\Exception\PositionNotFound;
use ju1ius\Pegasus\Utils\Str;

final class SourceInfo
{
    private int $sourceLength;
    /**
     * The line info array (0-based offsets)
     * [
     *   int $lineno => [int $startOffset, int $endOffset, int $length, string $line],
     *   ...
     * ]
     */
    private array $lines = [];
    private int $numLines = 0;

    public function __construct(
        private string $source,
        private int $tabSize = 4
    ) {
        $this->sourceLength = \strlen($this->source);
    }

    /**
     * Lazily initialises the line info array.
     *
     * @return array
     */
    private function getLines(): array
    {
        if (!$this->lines) {
            $lines = preg_split('/\R/', $this->source, -1, PREG_SPLIT_OFFSET_CAPTURE);
            foreach ($lines as $i => [$line, $offset]) {
                $length = \strlen($line);
                $this->lines[$i] = [$offset, $offset + $length, $length, $line];
            }
            $this->numLines = \count($lines);
        }

        return $this->lines;
    }

    public function getLine(int $lineno): string
    {
        $lines = $this->getLines();
        return $lines[$lineno][3];
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
     * @return int[]
     */
    public function positionFromOffset(int $offset): array
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
        [$bol, $eol, $length] = $lines[$line];
        if ($column > $eol - $bol) {
            throw new PositionNotFound($line, $column);
        }
        return $bol + $column;
    }


    /**
     * @param int $pos Byte offset of the source position to highlight
     * @param int $before Number of preceding context lines to show
     * @param int $after Number of following context lines to show
     * @param int $width
     * @return string
     */
    public function getExcerpt(int $pos, int $before = 1, int $after = 0, int $width = 80): string
    {
        [$line, $column] = $this->positionFromOffset($pos);
        $targetLine = $this->getLine($line);
        $firstLineNo = $line;
        $linesBefore = [];
        $linesAfter = [];
        $gutterWidth = \strlen((string)$this->numLines);
        $gutterSeparator = '│ ';
        $lineFormat = "%{$gutterWidth}d{$gutterSeparator}%s";
        $maxLineLength = $width - $gutterWidth - mb_strlen($gutterSeparator, 'utf-8');

        if ($before > 0 && $line - $before >= 0) {
            $linesBefore = $this->slice($line - $before, $before);
            $firstLineNo = key($linesBefore);
        }
        if ($after > 0) {
            $linesAfter = $this->slice($line + 1, $after);
        }

        $output = [
            sprintf("Line %d, column %d:", $line + 1, $column + 1),
        ];
        if ($firstLineNo > 1) {
            $pad = str_repeat(' ', $gutterWidth - 1);
            $output[] = "{$pad}…│ …";
        }

        // handle lines before
        foreach ($linesBefore as $lineno => $value) {
            $output[] = sprintf(
                $lineFormat,
                $lineno + 1,
                Str::truncate($value, $maxLineLength)
            );
        }
        // handle target line
        $output[] = sprintf(
            $lineFormat,
            $line + 1,
            Str::truncate($targetLine, $maxLineLength, $column)
        );
        // handle position indicator
        $pad = str_repeat('─', $gutterWidth);
        $output[] = sprintf("{$pad}┴╌%s┘", str_repeat('╌', $column));
        // handle lines after
        foreach ($linesAfter as $lineno => $value) {
            $output[] = sprintf(
                $lineFormat,
                $lineno + 1,
                Str::truncate($value, $maxLineLength)
            );
        }

        return implode("\n", $output);
    }

    /**
     * Performs a binary search on the line info array to find the line of the given position.
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
