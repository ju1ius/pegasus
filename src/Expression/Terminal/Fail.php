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
use ju1ius\Pegasus\Parser\Scope;

/**
 * An expression that always fail without consuming any input.
 *
 * This can be used to signal malformed input.
 */
class Fail extends Terminal
{
    public function __toString(): string
    {
        return '#FAIL';
    }

    public function isCapturing(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function match(string $text, Parser $parser)
    {
        $parser->registerFailure($this, $parser->pos);
    }
}
