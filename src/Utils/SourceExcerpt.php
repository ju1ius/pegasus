<?php declare(strict_types=1);
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Utils;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
final class SourceExcerpt
{
    /**
     * @var string
     */
    private $source;

    /**
     * @var array
     */
    private $sourceLines;

    /**
     * @var int
     */
    private $extractLength;

    /**
     * @var int|null
     */
    private $maxCols;

    /**
     * @param string $source
     * @param int    $numLines Number of lines to show.
     * @param int    $maxCols Max width of the displayed lines
     */
    public function __construct(string $source, int $numLines = 2, int $maxCols = 80)
    {
        $this->source = $source;
        $this->extractLength = $numLines;
        $this->maxCols = $maxCols;
    }

    /**
     * @param int $pos Byte offset of the source position to highlight
     *
     * @return string
     */
    public function getExcerpt(int $pos): string
    {
        $length = strlen($this->source);
        $lineInfo = $this->findLine($pos, $length);
        $column = $pos - $lineInfo['start'];
        $lines = $this->getLinesSlice($lineInfo['lineno']);
        $firstLineNo = key($lines) + 1;
        $targetLine = array_pop($lines);

        // handle lines before
        foreach ($lines as $lineno => $line) {
            $lines[$lineno] = sprintf(
                '%4d│ %s',
                $lineno + 1,
                Str::truncate($line[0], $this->maxCols - 6)
            );
        }
        // handle target line
        $lines[] = sprintf(
            '%4d│ %s',
            $lineInfo['lineno'] + 1,
            Str::truncate($targetLine[0], $this->maxCols - 6, $column)
        );

        $text = sprintf("Line %d, column %d:\n", $lineInfo['lineno'] + 1, $column + 1);
        if ($firstLineNo > 1) {
            $text .= "   …│  …\n";
        }
        $text .= implode("\n", $lines);
        $text .= sprintf("\n────┴╌%s┘", str_repeat('╌', $column));

        return $text;
    }

    /**
     * @return string[]
     */
    private function getLines(): array
    {
        if (!$this->sourceLines) {
            $this->sourceLines = preg_split('/\R/', $this->source, -1, PREG_SPLIT_OFFSET_CAPTURE);
        }

        return $this->sourceLines;
    }

    /**
     * @param int $lineno
     * @return string[]
     */
    private function getLinesSlice(int $lineno): array
    {
        $lines = $this->getLines();
        $numLines = count($lines);
        $start = max(0, $lineno - $this->extractLength + 1);
        $length = min($numLines, $lineno - $start + 1);

        return array_slice($lines, $start, $length, true);
    }

    /**
     * Performs a binary search on the lines info array to find the line number of the given position.
     *
     * @param int $pos
     * @param int $length
     *
     * @return array|null
     * @todo throw SourceLineNotFound
     */
    private function findLine(int $pos, int $length): ?array
    {
        $lines = $this->getLines();
        $numLines = count($lines);
        $minLine = 0;
        $maxLine = $numLines;
        $i = (int)floor($numLines / 2);
        $result = null;

        while (true) {
            [$line, $bol] = $lines[$i];
            $eol = ($i === $numLines - 1) ? $length : $lines[$i + 1][1];
            if ($pos >= $bol && $pos < $eol) {
                $result = [
                    'line' => $line,
                    'lineno' => $i,
                    'start' => $bol,
                    'end' => $eol,
                ];
                break;
            }
            if ($pos < $bol) {
                $maxLine = $i;
            } elseif ($pos > $bol) {
                $minLine = $i;
            }
            $i = $minLine + (int)floor(($maxLine - $minLine) / 2);
        }

        return $result;
    }
}
