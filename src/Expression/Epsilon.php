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
use ju1ius\Pegasus\Parser\Scope;

/**
 * The empty string
 *
 * Always matches without consuming any input.
 */
class Epsilon extends Terminal
{
    public function __toString()
    {
        return 'ε';
    }

    public function isCapturing()
    {
        return false;
    }

    public function match($text, $pos, ParserInterface $parser, Scope $scope)
    {
        return new Node\Transient($pos, $pos);
    }
}
