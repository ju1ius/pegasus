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

use ju1ius\Pegasus\Expression\Terminal;
use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\Parser\Parser;


/**
 * The empty string
 *
 * Always matches without consuming any input.
 */
class Epsilon extends Terminal
{
    public function __toString(): string
    {
        return 'ε';
    }

    public function isCapturing(): bool
    {
        return false;
    }

    public function match(string $text, Parser $parser)
    {
        return true;
    }
}
