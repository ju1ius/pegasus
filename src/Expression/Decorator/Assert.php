<?php declare(strict_types=1);
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Expression\Decorator;

use ju1ius\Pegasus\Expression\Decorator;
use ju1ius\Pegasus\Parser\Parser;


/**
 * Decorates an expression and succeeds or fails like the decorated expression,
 * but never consumes any input (zero-width positive lookahead).
 *
 * @author ju1ius <ju1ius@laposte.net>
 */
final class Assert extends Decorator
{
    public function __toString(): string
    {
        return sprintf('&:%s', $this->stringChildren()[0]);
    }

    public function isCapturing(): bool
    {
        return false;
    }

    public function isCapturingDecidable(): bool
    {
        return true;
    }

    public function match(string $text, Parser $parser)
    {
        $start = $parser->pos;
        $capturing = $parser->isCapturing;

        $parser->isCapturing = false;
        $result = $this->children[0]->match($text, $parser);

        $parser->isCapturing = $capturing;
        $parser->pos = $start;

        return !!$result;
    }
}
