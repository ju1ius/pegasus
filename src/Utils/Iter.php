<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Utils;

use Countable;
use Traversable;

final class Iter
{
    public static function of(array|Traversable $value): Traversable
    {
        if (\is_array($value)) {
            return new \ArrayIterator($value);
        }
        return $value;
    }

    public static function map(callable $fn, iterable $col): Traversable
    {
        foreach ($col as $k => $v) {
            yield $fn($v, $k, $col);
        }
    }

    public static function filter(callable $fn, iterable $col): Traversable
    {
        foreach ($col as $k => $v) {
            if ($fn($v, $k, $col)) {
                yield $k => $v;
            }
        }
    }

    public static function every(callable $fn, iterable $col): bool
    {
        foreach ($col as $i => $value) {
            if (!$fn($value, $i, $col)) {
                return false;
            }
        }

        return true;
    }

    public static function some(callable $fn, iterable $col): bool
    {
        foreach ($col as $i => $value) {
            if ($fn($value, $i, $col)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the first value in collection for which predicate returns truthy.
     */
    public static function find(callable $fn, iterable $col): mixed
    {
        foreach ($col as $k => $v) {
            if ($fn($v, $k, $col)) {
                return $v;
            }
        }

        return null;
    }

    /**
     * Yields items from a collection grouped in chunks (n-tuples) of the given size.
     * Iteration stops when a chunk of the given size cannot be produced.
     * @return Traversable<array>
     */
    public static function consecutive(int $size, Traversable&Countable $collection): Traversable
    {
        $max = \count($collection) - 1;
        if ($size > $max + 1) {
            return;
        }
        foreach ($collection as $i => $v) {
            $tuple = [];
            for ($j = 0; $j < $size; $j++) {
                if ($i + $j > $max) {
                    return;
                }
                $tuple[] = $collection[$i + $j];
            }
            yield $tuple;
        }
    }

    /**
     * Iterates over `$collection` in consecutive chunks of `$size` and returns true if,
     * for any chunk, `$predicate` returns true for all items in the chunk.
     */
    public static function someConsecutive(callable $predicate, int $size, Traversable&Countable $collection): bool
    {
        foreach (self::consecutive($size, $collection) as $tuple) {
            if (self::every($predicate, $tuple)) {
                return true;
            }
        }

        return false;
    }
}
