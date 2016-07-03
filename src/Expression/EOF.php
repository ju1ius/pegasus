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

use ju1ius\Pegasus\Parser\ParserInterface;
use ju1ius\Pegasus\Node;
use ju1ius\Pegasus\Parser\Scope;

/**
 * Matches if there's nothing left to consume (end of input).
 */
class EOF extends Terminal
{
    public function __construct()
    {
        parent::__construct('EOF');
    }

    public function isCapturing()
    {
        return false;
    }

    public function match($text, $pos, ParserInterface $parser, Scope $scope)
    {
        if (!isset($text[$pos])) {
            return new Node\EOF($this, $text, $pos, $pos);
        }
    }

    public function __toString()
    {
        return 'EOF';
    }
}
