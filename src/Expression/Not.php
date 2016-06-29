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
 * An expression that succeeds only if the expression within it doesn't
 *
 * In any case, it never consumes any characters;
 * it's a negative lookahead.
 */
class Not extends Wrapper
{
    public function asRightHandSide()
    {
        return sprintf('!(%s)', $this->stringMembers());
    }

    public function isCapturing()
    {
        return false;
    }

    public function isCapturingDecidable()
    {
        return true;
    }

    public function match($text, $pos, ParserInterface $parser)
    {
        $node = $parser->apply($this->children[0], $pos);
        if (!$node) {
            return new Node\Not($this, $text, $pos, $pos);
        }
    }
}
