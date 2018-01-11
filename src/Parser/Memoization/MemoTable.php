<?php declare(strict_types=1);


namespace ju1ius\Pegasus\Parser\Memoization;


use ju1ius\Pegasus\Expression;


abstract class MemoTable
{
    /**
     * @var int
     */
    protected $stored = 0;

    /**
     * @var int
     */
    protected $used = 0;

    /**
     * @var int
     */
    protected $invalidated = 0;

    abstract public function has(int $pos, Expression $expr): bool;

    abstract public function get(int $pos, Expression $expr): ?MemoEntry;

    abstract public function set(int $pos, Expression $expr, $result): MemoEntry;

    abstract public function clear(?int $pos = null): void;

    public function stats(): array
    {
        return [
            'stored' => $this->stored,
            'used' => $this->used,
            'invalidated' => $this->invalidated,
        ];
    }
}