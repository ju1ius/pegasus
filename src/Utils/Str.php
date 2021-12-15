<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Utils;

final class Str
{
    public static function className(object|string $value, int $namespaces = 0): string
    {
        if (\is_object($value)) {
            $value = get_class($value);
        }
        $components = explode('\\', $value);
        $components = array_slice($components, -($namespaces + 1));

        return implode('\\', $components);
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
