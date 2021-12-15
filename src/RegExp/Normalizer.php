<?php declare(strict_types=1);


namespace ju1ius\Pegasus\RegExp;


class Normalizer
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

    public static function normalize(string $pattern, array $initialFlags = ['x']): string
    {
        $initialModifiers  = self::getInitialModifiers($initialFlags);

        // 1. preprocessing
        $groups = (new PCREGroupInfo())->parse($pattern);
        $modifierGroups = [];
        foreach ($groups as $i => $group) {
            $type = $group['type'];
            if ($type === 'comment') {
                // 1.2 Remove inline comments
                $pattern = str_replace($group['pattern'], '', $pattern);
            } else if ($type === 'setopt') {
                // 1.3 collect groups that can change modifiers
                $modifierGroups[] = $group;
            }
        }

        // 2. remove whitespace and block comments
        $pos = 0;
        $end = \strlen($pattern);
        $output = '';
        $insideClass = false;
        while ($pos < $end) {
            $char = $pattern[$pos];
            // Check if the PCRE_EXTENDED option is currently unset by a group.
            $modifiers = self::getCurrentModifiers($pos, $modifierGroups, $initialModifiers);
            if (!$modifiers['x']) {
                $output .= $char;
                $pos++;
                continue;
            }

            switch ($char) {
                case '\\':
                    $output .= substr($pattern, $pos, 2);
                    $pos += 2;
                    continue 2;
                case '[':
                    if ($insideClass && preg_match(self::POSIX_CLASS_RX, $pattern, $matches, 0, $pos)) {
                        $output .= $matches[0];
                        $pos += \strlen($matches[0]);
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
                        $pos += \strlen($matches[0]);
                        continue 2;
                    }
                    break;
                default:
                    if (!$insideClass && preg_match('/\G\s+/', $pattern, $matches, 0, $pos)) {
                        $pos += \strlen($matches[0]);
                        continue 2;
                    }
                    break;
            }
            $output .= $char;
            $pos++;
        }

        return $output;
    }

    private static function getCurrentModifiers(int $pos, array $groups, array $initialModifiers)
    {
        $modifiers = $initialModifiers;
        foreach ($groups as $group) {
            if ($group['type'] === 'setopt' && $pos > $group['applies_from'] && $pos < $group['applies_until']) {
                $modifiers = array_merge($modifiers, $group['options']);
            }
        }

        return $modifiers;
    }

    private static function getInitialModifiers(array $initialFlags)
    {
        // ensure the x flag is always set
        $modifiers = ['x' => false];
        foreach ($initialFlags as $flag) {
            $modifiers[$flag] = true;
        }

        return $modifiers;
    }
}
