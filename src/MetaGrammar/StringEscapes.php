<?php declare(strict_types=1);

namespace ju1ius\Pegasus\MetaGrammar;

final class StringEscapes
{
    private const ESCAPES_RE = <<<'REGEXP'
    /
        (?<!\\)
        (?<bs>(?: \\\\ )*)  # zero-or-more even number of backslashes
        \\                  # literal backslash
        (?:
            (?<char>[nrtvf"'])
            |
            u { (?<codepoint> [0-9A-Fa-f]{1,6} ) }
        )
    /x
    REGEXP;

    public static function unescape(string $input): string
    {
        return preg_replace_callback(self::ESCAPES_RE, static function($matches) {
            $backslashes = $matches['bs'] ?? '';
            $result = '';
            if (isset($matches['char'])) {
                $escape = sprintf('\%s', $matches['char']);
                $result = stripcslashes($escape);
            } else if (isset($matches['codepoint'])) {
                $codepoint = hexdec($matches['codepoint']);
                $result = \IntlChar::chr($codepoint);
                if ($result === null) {
                    return $matches[0];
                }
            }

            return sprintf('%s%s', $backslashes, $result);
        }, $input);
    }
}
