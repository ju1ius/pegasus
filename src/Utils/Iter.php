<?php
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
final class Iter
{
    /**
     * @param callable           $fn
     * @param array|\Traversable $col
     *
     * @return bool
     */
    public static function every(callable $fn, $col)
    {
        foreach ($col as $i => $value) {
            if (!$fn($value, $i, $col)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param callable           $fn
     * @param array|\Traversable $col
     *
     * @return bool
     */
    public static function some(callable $fn, $col)
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
     * @param callable           $fn
     * @param array|\Traversable $col
     *
     * @return mixed
     */
    public static function find(callable $fn, $col)
    {
        foreach ($col as $k => $v) {
            if ($fn($v, $k, $col)) {
                return $v;
            }
        }

        return null;
    }

    /**
     * @param int $size
     * @param     $col
     *
     * @return \Generator
     */
    public static function consecutive($size, $col)
    {
        $max = count($col) - 1;
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
     * @param int      $size
     * @param          $collection
     *
     * @return bool
     */
    public static function someConsecutive(callable $predicate, $size, $collection)
    {
        foreach (self::consecutive($size, $collection) as $tuple) {
            if (self::every($predicate, $tuple)) {
                return true;
            }
        }

        return false;
    }
}
