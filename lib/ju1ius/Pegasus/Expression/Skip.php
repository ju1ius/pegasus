<?php

namespace ju1ius\Pegasus\Expression;

use ju1ius\Pegasus\Node;
use ju1ius\Pegasus\Parser\ParserInterface;


/**
 * Expression that skips over what his sub-expression matches.
 *
 * This is equivalent to igoring nodes in the node visitor,
 * but it can dramatically reduce the size of the parse tree.
 */
class Skip extends Wrapper
{
    public function asRhs()
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
