<?php

namespace ju1ius\Pegasus\Expression;

use ju1ius\Pegasus\Parser\ParserInterface;
use ju1ius\Pegasus\Node;


/**
 * A series of expressions that must match contiguous,
 * ordered pieces of the text.
 *
 * In other words, it's a concatenation operator:
 * each piece has to match, one after another.
 */
class Sequence extends Composite
{
    public function asRhs()
    {
        return implode(' ', $this->stringMembers());
    }

    public function getCaptureCount()
    {
        $capturing = 0;
        foreach ($this->members as $child) {
            if ($child->isCapturing()) {
                $capturing++;
            }
        }

        return $capturing;
    }
    
    public function match($text, $pos, ParserInterface $parser)
    {
        $new_pos = $pos;
        $seq_len = 0;
        $children = [];
        foreach ($this->members as $member) {
            $node = $parser->apply($member, $new_pos);
            if (!$node) {
                return;
            }
            $children[] = $node;
            $len = $node->end - $node->start;
            $new_pos += $len;
            $seq_len += $len;
        }
        return new Node\Sequence($this, $text, $pos, $pos + $seq_len, $children);
    }
}
