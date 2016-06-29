<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace ju1ius\Pegasus\Expression;

use ju1ius\Pegasus\Node;
use ju1ius\Pegasus\Parser\ParserInterface;

/**
 * An expression that always fail without consuming any input.
 *
 * This can be used to signal malformed input.
 */
class Fail extends Terminal
{
    /**
     * @inheritdoc
     */
    public function asRightHandSide()
    {
		return '#FAIL';
    }

    /**
     * @inheritdoc
     */
    public function isCapturing()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function match($text, $pos, ParserInterface $parser)
    {
        return null;
    }
}
