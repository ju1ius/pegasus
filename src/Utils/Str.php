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
final class Str
{
    /**
     * @param object|string $value
     *
     * @return string
     */
    public static function className($value)
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
}
