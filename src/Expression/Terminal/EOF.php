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
use ju1ius\Pegasus\Parser\Parser;


/**
 * Matches if there's nothing left to consume (end of input).
 */
final class EOF extends Terminal
{
    public function __construct()
    {
        parent::__construct('EOF');
    }

    public function isCapturing(): bool
    {
        return false;
    }

    public function match(string $text, Parser $parser)
    {
        $start = $parser->pos;
        return !isset($text[$start]);
    }

    public function __toString(): string
    {
        return 'EOF';
    }
}
