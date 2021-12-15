<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Utils;

final class Iter
{
    public static function map(callable $fn, iterable $col): \Generator
    {
        foreach ($col as $k => $v) {
            yield $fn($v, $k, $col);
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
     *
     * @param callable $fn
     * @param iterable $col
     *
     * @return mixed
     */
    public static function find(callable $fn, iterable $col)
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
     *
     * @param int $size
     * @param iterable $col
     *
     * @return \Generator
     */
    public static function consecutive($size, iterable $col): \Generator
    {
        $max = \count($col) - 1;
        if ($size > $max + 1) {
            return;
        }
        foreach ($col as $i => $v) {
            $tuple = [];
            for ($j = 0; $j < $size; $j++) {
                if ($i + $j > $max) {
                    return;
                }
                $tuple[] = $col[$i + $j];
            }
            yield $tuple;
        }
    }

    /**
     * Iterates over `$collection` in consecutive chunks of `$size` and returns true if,
     * for any chunk, `$predicate` returns true for all items in the chunk.
     *
     * @param callable $predicate
     * @param int $size
     * @param iterable $col
     *
     * @return bool
     */
    public static function someConsecutive(callable $predicate, int $size, iterable $col): bool
    {
        foreach (self::consecutive($size, $col) as $tuple) {
            if (self::every($predicate, $tuple)) {
                return true;
            }
        }

        return false;
    }
}
