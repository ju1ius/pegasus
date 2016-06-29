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
 * A series of expressions, one of which must match
 *
 * Expressions are tested in order from first to last.
 * The first to succeed wins.
 */
class OneOf extends Composite
{
    public function asRightHandSide()
    {
        return implode(' | ', $this->stringMembers());
    }

    public function isCapturingDecidable()
    {
        $capturingChildren = 0;
        foreach ($this->children as $child) {
            if (!$child->isCapturingDecidable()) {
                return false;
            }
            if ($child->isCapturing()) {
                $capturingChildren++;
            }
        }

        return !$capturingChildren || $capturingChildren === count($this->children);
    }

    public function match($text, $pos, ParserInterface $parser)
    {
        foreach ($this->children as $child) {
            $node = $parser->apply($child, $pos);
            if ($node) {
                // Wrap the succeeding child in a node representing the OneOf
                return new Node\OneOf($this, $text, $pos, $node->end, [$node]);
            }
        }
    }
}