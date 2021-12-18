<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Parser\Memoization;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Grammar;

/**
 * MemoTable implementing the sliding-window algorithm by Kimio Kuramitsu.
 *
 * @see http://doi.org/10.2197/ipsjjip.23.505
 */
final class SlidingMemoTable extends MemoTable
{
    const DEFAULT_WIDTH = 64;

    private int $size;

    /**
     * @var MemoEntry[]
     */
    private array $entries = [];

    /**
     * @var int[]
     */
    private array $rules = [];

    private int $shift;

    public function __construct(Grammar $grammar, int $width = self::DEFAULT_WIDTH)
    {
        $height = $this->collectRules($grammar);
        $this->size = $width * $height + 1;
        $this->entries = [];
        $this->shift = (int)floor(log($height) / log(2) + 1);
    }

    public function getWindowSize()
    {
        return $this->size;
    }

    public function has(int $pos, Expression $expr): bool
    {
        //$hash = $this->hash($pos, $expr);
        $hash = (($pos << $this->shift) | $this->rules[$expr->id]) & \PHP_INT_MAX;
        $index = $hash % $this->size;

        return isset($this->entries[$index]) && $this->entries[$index]->hash === $hash;
    }

    public function get(int $pos, Expression $expr): ?MemoEntry
    {
        //$hash = $this->hash($pos, $expr);
        $hash = (($pos << $this->shift) | $this->rules[$expr->id]) & \PHP_INT_MAX;
        $index = $hash % $this->size;
        $memo = $this->entries[$index] ?? null;

        if ($memo && $memo->hash === $hash) {
            $this->hits++;
            return $memo;
        }

        $this->misses++;

        return null;
    }

    public function set(int $pos, Expression $expr, $result): MemoEntry
    {
        //$hash = $this->hash($pos, $expr);
        $hash = (($pos << $this->shift) | $this->rules[$expr->id]) & \PHP_INT_MAX;
        $index = $hash % $this->size;
        $memo = $this->entries[$index] ?? null;

        if ($memo) {
            $memo->hash = $hash;
            $memo->end = $pos;
            $memo->result =  $result;
        } else {
            $memo = new MemoEntry($pos, $result);
            $memo->hash = $hash;
            $this->entries[$index] = $memo;
        }
        $this->storages++;

        return $memo;
    }

    public function cut(int $pos): void
    {
        foreach ($this->entries as $index => $memo) {
            if ($memo && $memo->end < $pos) {
                $this->invalidations++;
                unset($this->entries[$index]);
            }
        }
    }

    /**
     * Inlined for performance.
     *
     * @codeCoverageIgnore
     */
    private function hash(int $pos, Expression $expr)
    {
        return (($pos << $this->shift) | $this->rules[$expr->id]) & \PHP_INT_MAX;
    }

    private function collectRules(Grammar $grammar, int $n = 0): int
    {
        foreach ($grammar as $expr) {
            if (!isset($this->rules[$expr->id])) {
                $this->rules[$expr->id] = $n++;
            }
        }
        // Walk the inheritance chain
        if ($parent = $grammar->getParent()) {
            $n = $this->collectRules($parent, $n);
        }
        foreach ($grammar->getTraits() as $imported) {
            $n = $this->collectRules($imported, $n);
        }

        return $n;
    }
}
