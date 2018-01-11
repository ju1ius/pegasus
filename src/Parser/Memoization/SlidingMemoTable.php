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

    /**
     * @var int
     */
    private $size;

    /**
     * @var MemoEntry[]
     */
    private $entries = [];

    /**
     * @var int[]
     */
    private $rules = [];

    /**
     * @var int
     */
    private $shift;

    public function __construct(Grammar $grammar, int $width = self::DEFAULT_WIDTH)
    {
        $height = $this->collectRules($grammar);
        $this->size = $width * $height + 1;
        $this->entries = [];
        $this->shift = floor((log($height) / log(2)) + 1);
    }

    public function getWindowSize()
    {
        return $this->size;
    }

    public function has(int $pos, Expression $expr): bool
    {
        //$key = $this->key($pos, $expr);
        $key = (($pos << $this->shift) | $this->rules[$expr->id]) & \PHP_INT_MAX;
        $hash = $key % $this->size;

        return isset($this->entries[$hash]) && $this->entries[$hash]->key === $key;
    }

    public function get(int $pos, Expression $expr): ?MemoEntry
    {
        //$key = $this->key($pos, $expr);
        $key = (($pos << $this->shift) | $this->rules[$expr->id]) & \PHP_INT_MAX;
        $hash = $key % $this->size;
        $memo = $this->entries[$hash] ?? null;
        if ($memo && $memo->key === $key) {
            $this->used++;
            return $memo;
        }

        return null;
    }

    public function set(int $pos, Expression $expr, $result): MemoEntry
    {
        //$key = $this->key($pos, $expr);
        $key = (($pos << $this->shift) | $this->rules[$expr->id]) & \PHP_INT_MAX;
        $hash = $key % $this->size;
        $memo = $this->entries[$hash] ?? null;

        if ($memo) {
            $memo->key = $key;
            $memo->end = $pos;
            $memo->result =  $result;
        } else {
            $memo = new MemoEntry($pos, $result);
            $memo->key = $key;
            $this->entries[$hash] = $memo;
        }
        $this->stored++;

        return $memo;
    }

    public function clear(?int $pos = null): void
    {
        if ($pos === null) {
            $this->entries = [];
            return;
        }
        foreach ($this->entries as $i => $memo) {
            if ($memo && $memo->end < $pos) {
                $this->invalidated++;
                unset($this->entries[$i]);
            }
        }
    }

    public function stats(): array
    {
        return [
            'stored' => $this->stored,
            'used' => $this->used,
            'invalidated' => $this->invalidated,
        ];
    }

    /**
     * Inlined for performance.
     *
     * @codeCoverageIgnore
     */
    private function key(int $pos, Expression $expr)
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
        foreach ($grammar->getImports() as $imported) {
            $n = $this->collectRules($imported, $n);
        }

        return $n;
    }
}