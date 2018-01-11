<?php declare(strict_types=1);


namespace ju1ius\Pegasus\Parser\Memoization;


use ju1ius\Pegasus\Expression;


final class NullMemoTable extends MemoTable
{
    public function has(int $pos, Expression $expr): bool
    {
        return false;
    }

    public function get(int $pos, Expression $expr): ?MemoEntry
    {
        return null;
    }

    public function set(int $pos, Expression $expr, $result): MemoEntry
    {
        $this->stored++;

        return new MemoEntry($pos, $result);
    }

    public function clear(?int $pos = null): void
    {

    }
}