<?php declare(strict_types=1);
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Expression\Terminal;

use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\Parser\Parser;

/**
 * An expression that matches what a regex does.
 *
 * Use these as much as you can and jam as much into each one as you can: they're fast.
 */
final class RegExp extends PCREPattern
{
    public function match(string $text, Parser $parser)
    {
        $start = $parser->pos;
        if (preg_match($this->compiledPattern, $text, $matches, 0, $start)) {
            $match = $matches[0];
            $end = $parser->pos += strlen($match);
            if (!$parser->isCapturing) {
                return true;
            }

            return $parser->isCapturing
                ? new Node\Terminal($this->name, $start, $end, $match, ['groups' => $matches])
                : true;
        }

        $parser->registerFailure($this, $start);
    }
}
