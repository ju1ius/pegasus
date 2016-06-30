<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Utils;

final class StringUtil
{
    const BACKREF_SPLIT_RX = <<<'EOS'
@
    ((?: \\. | [^\$] )*?)    # Any escaped char or not $
    \$\{ ([a-zA-Z_]\w*) \}    # ${identifier}
    ((?: \\. | [^\$] )*)    # Any escaped char or not $
@Sx
EOS;

    const ESCAPES_REPLACE_RX = <<<'EOS'
@
    (?<!\\\\) \\\\ ((?:\\\\\\\\)*)    # odd number of backslashes
    (?:                             # followed by
        x([0-9a-fA-F]{1,2})            # hex escape sequence
        | ([abnrtvef])                # or control char escape sequence
        | ([0-7]{1,3})              # or octal escape sequence
    )
@Sx
EOS;

    const BACKSLASH_REPLACE_RX = <<<'EOS'
@
    (?<!\\\\)((?:\\\\\\\\)+)    # even number of backslashes
    (                           # followed by
        x[0-9a-fA-F]{1,2}       # hex sequence
        |[abnrtvef]             # or ctrl char
        |[0-7]{1,3}             # or octal sequence
    )
@Sx
EOS;

    public static function splitBackReferenceSubject($subject)
    {
        if (preg_match_all(self::BACKREF_SPLIT_RX, $subject, $matches, PREG_SET_ORDER)) {
            return $matches;
        }
    }

    public static function replaceBackReferenceSubject(array $subjectParts, callable $callback, $regex = false)
    {
        $output = '';
        foreach ($subjectParts as $part) {
            $replaced = $callback($part[2]);
            if ($regex && $replaced) {
                $replaced = preg_quote($replaced, '/');
            }
            $output .= $part[1] . ($replaced ?: '') . $part[3];
        }

        return $output;
    }

    public static function convertEscapeSequences($str)
    {
        // replace php style escape sequences by their actual character
        $str = preg_replace_callback(self::ESCAPES_REPLACE_RX, [self::class, 'escapeReplaceCallback'], $str);
        // reduce redundant backslashes
        // FIXME: use stripcslashes ?
        $str = preg_replace_callback(self::BACKSLASH_REPLACE_RX, [self::class, 'backslashReplaceCallback'], $str);

        return $str;
    }

    private static function escapeReplaceCallback($matches)
    {
        $res = str_repeat('\\', strlen($matches[1]) / 2);
        if (isset($matches[4])) {
            $res .= chr(octdec($matches[4]));
        } elseif (isset($matches[3])) {
            switch ($matches[3]) {
                case 'a':
                    $res .= "\x07";
                    break;
                case 'b':
                    $res .= "\x08";
                    break;
                case 'n':
                    $res .= "\n";
                    break;
                case 'r':
                    $res .= "\r";
                    break;
                case 't':
                    $res .= "\t";
                    break;
                case 'v':
                    $res .= "\v";
                    break;
                case 'e':
                    $res .= "\e";
                    break;
                case 'f':
                    $res .= "\f";
                    break;
            }
        } elseif (isset($matches[2])) {
            $res .= chr(hexdec($matches[2]));
        } else {
            $res = $matches[0];
        }

        return $res;
    }

    private static function backslashReplaceCallback($matches)
    {
        return str_repeat('\\', strlen($matches[1]) / 2) . $matches[2];
    }
}
