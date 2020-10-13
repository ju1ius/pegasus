<?php declare(strict_types=1);


namespace ju1ius\Pegasus\Parser\Memoization;


use ju1ius\Pegasus\Expression;


abstract class MemoTable
{
    protected int $storages = 0;
    protected int $hits = 0;
    protected int $misses = 0;
    protected int $invalidations = 0;

    abstract public function has(int $pos, Expression $expr): bool;

    abstract public function get(int $pos, Expression $expr): ?MemoEntry;

    abstract public function set(int $pos, Expression $expr, $result): MemoEntry;

    abstract public function cut(int $pos): void;

    public function stats(): array
    {
        return [
            'stored' => $this->storages,
            'hits' => $this->hits,
            'misses' => $this->misses,
            'invalidations' => $this->invalidations,
        ];
    }
}
