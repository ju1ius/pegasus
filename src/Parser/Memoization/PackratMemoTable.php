<?php declare(strict_types=1);


namespace ju1ius\Pegasus\Parser\Memoization;


use ju1ius\Pegasus\Expression;


final class PackratMemoTable extends MemoTable
{
    private $entries = [];

    public function has(int $pos, Expression $expr): bool
    {
        return isset($this->entries[$pos][$expr->id]);
    }

    public function get(int $pos, Expression $expr): ?MemoEntry
    {
        $memo = $this->entries[$pos][$expr->id] ?? null;
        if ($memo) {
            $this->hits++;
        } else {
            $this->misses++;
        }

        return $memo;
    }

    public function set(int $pos, Expression $expr, $result): MemoEntry
    {
        $memo = new MemoEntry($pos, $result);
        $this->entries[$pos][$expr->id] = $memo;
        $this->storages++;

        return $memo;
    }

    public function cut(int $pos): void
    {
        foreach ($this->entries as $i => $ids) {
            if ($i < $pos) {
                unset($this->entries[$i]);
                $this->invalidations++;
            }
        }
    }
}
