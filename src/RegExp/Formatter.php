<?php declare(strict_types=1);


namespace ju1ius\Pegasus\RegExp;


class Formatter
{
    private const POSIX_CLASS_RX = <<<'REGEXP'
/\G
    \[:
        (?:
            alnum	    # letters and digits
            | alpha	    # letters
            | ascii	    # character codes 0 - 127
            | blank	    # space or tab only
            | cntrl	    # control characters
            | digit	    # decimal digits (same as \d)
            | graph	    # printing characters, excluding space
            | lower	    # lower case letters
            | print	    # printing characters, including space
            | punct	    # printing characters, excluding letters and digits
            | space	    # white space (not quite the same as \s)
            | upper	    # upper case letters
            | word	    # "word" characters (same as \w)
            | xdigit    # hexadecimal digits
        )
    :]
/Sx
REGEXP;

    public static function removeComments(string $pattern): string
    {
        $mayHaveComments = strpos($pattern, '#') !== false;
        // 1. remove inline comments
        if ($mayHaveComments) {
            $groupInfo = (new PCREGroupInfo())->parse($pattern);
            foreach ($groupInfo as ['type' => $type, 'pattern' => $comment]) {
                if ($type === 'comment') {
                    $pattern = str_replace($comment, '', $pattern);
                }
            }
        }
        // 2. remove whitespace and block comments
        // TODO: what if the PCRE_EXTENDED option is unset by a group ?
        $pos = 0;
        $end = strlen($pattern);
        $output = '';
        $insideClass = false;
        while ($pos < $end) {
            $char = $pattern[$pos];
            switch ($char) {
                case '\\':
                    $output .= substr($pattern, $pos, 2);
                    $pos += 2;
                    continue 2;
                case '[':
                    if ($insideClass && preg_match(self::POSIX_CLASS_RX, $pattern, $matches, 0, $pos)) {
                        $output .= $matches[0];
                        $pos += strlen($matches[0]);
                        continue 2;
                    }
                    $insideClass = true;
                    break;
                case ']':
                    $insideClass = false;
                    break;
                case '#':
                    if (!$insideClass) {
                        preg_match('/\G#.*/', $pattern, $matches, 0, $pos);
                        $pos += strlen($matches[0]);
                        continue 2;
                    }
                    break;
                default:
                    if (!$insideClass && preg_match('/\G\s+/', $pattern, $matches, 0, $pos)) {
                        $pos += strlen($matches[0]);
                        continue 2;
                    }
                    break;
            }
            $output .= $char;
            $pos++;
        }

        return $output;
    }
}
