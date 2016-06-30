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
 * Expression that skips over what his sub-expression matches.
 *
 * This is equivalent to ignoring nodes in the node visitor, but it can dramatically reduce the size of the parse tree.
 */
class Skip extends Decorator
{
    public function asRightHandSide()
    {
        return sprintf('~(%s)', $this->stringMembers());
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
        if ($node = $parser->apply($this->children[0], $pos)) {
            return new Node\Skip($this, $text, $node->start, $node->end);
        }
    }
}
