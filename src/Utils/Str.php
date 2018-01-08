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
final class Str
{
    /**
     * @param object|string $value
     *
     * @return string
     */
    public static function className($value): string
    {
        if (is_object($value)) {
            $value = get_class($value);
        }
        $p = strrpos($value, '\\');

        if ($p === false) {
            return $value;
        }

        return substr($value, $p + 1);
    }

    /**
     * Truncate a string to `$maxWidth`, while keeping `$targetCol` visible.
     *
     * @param string $str
     * @param int    $maxWidth Maximum width of the string, in characters
     * @param int    $targetCol
     * @param string $leftMark
     * @param string $rightMark
     * @param string $encoding
     *
     * @return string
     */
    public static function truncate(
        string $str,
        int $maxWidth = 80,
        int $targetCol = 0,
        string $leftMark = '… ',
        string $rightMark = ' …',
        string $encoding = 'UTF-8'
    ): string {
        $width = mb_strlen($str, $encoding);
        if ($width <= $maxWidth) {
            return $str;
        }
        if ($targetCol <= $maxWidth - mb_strlen($rightMark, $encoding)) {
            // truncate right
            return mb_strimwidth($str, 0, $maxWidth, $rightMark, $encoding);
        }

        // truncate left
        return $leftMark . mb_strimwidth($str, mb_strlen($leftMark), $targetCol, '', $encoding);
    }
}
