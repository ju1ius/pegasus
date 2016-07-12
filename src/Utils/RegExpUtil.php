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
class RegExpUtil
{
    const HAS_CAPTURING_GROUP_RX = <<<'REGEXP'
/
    (?<!\\\\) (?:\\\\\\\\)*             # even number of backslashes?
    \(                                  # open parenthesis
    (?:
        \? (?: P? <[^>]+> | '[^']+')    # named subpattern
        |                               # or
        (?! \? )                        # not a '?' (it's a numbered subpattern)
    )
/Sx
REGEXP;

    /**
     * Returns whether the pattern contains capturing groups.
     *
     * @param string $pattern
     *
     * @return bool
     */
    public static function hasCapturingGroups($pattern)
    {
        if (preg_match(self::HAS_CAPTURING_GROUP_RX, $pattern)) {
            return true;
        }

        return false;
    }

    public static function countCapuringGroups($pattern)
    {
        // TODO: implement this method!
    }
}
