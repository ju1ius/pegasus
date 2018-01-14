<?php declare(strict_types=1);


namespace ju1ius\Pegasus\RegExp;


class Formatter
{
    public static function removeComments(string $pattern): string
    {
        $pos = 0;
        $end = strlen($pattern);
        $output = '';
        while ($pos < $end) {
            $char = $pattern[$pos];
            switch ($char) {
                case '\\':
                    $output .= substr($pattern, $pos, 2);
                    $pos += 2;
                    continue;
                case '#':
                    preg_match('/\G#.*/', $pattern, $matches, 0, $pos);
                    $pos += strlen($matches[0]);
                    continue;
                default:
                    if (preg_match('/\G\s+/', $pattern, $matches, 0, $pos)) {
                        $pos += strlen($matches[0]);
                        continue;
                    }
                    $pos++;
                    $output .= $char;
                    break;
            }
        }

        return $output;
    }
}
