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

use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Node;
use ju1ius\Pegasus\Parser\ParserInterface;
use ju1ius\Pegasus\Parser\Scope;

/**
 * A series of expressions that must match contiguous, ordered pieces of the text.
 *
 * In other words, it's a concatenation operator: each piece has to match, one after another.
 */
class Sequence extends Composite
{
    public function __toString()
    {
        return implode(' ', $this->stringMembers());
    }

    public function getCaptureCount()
    {
        $capturing = 0;
        foreach ($this->children as $child) {
            if ($child->isCapturing()) {
                $capturing++;
            }
        }

        return $capturing;
    }

    public function match($text, $pos, ParserInterface $parser, Scope $scope)
    {
        $nextPos = $pos;
        $totalLength = 0;
        $children = [];
        foreach ($this->children as $child) {
            $node = $parser->apply($child, $nextPos, $scope);
            if (!$node) {
                return null;
            }
            $length = $node->end - $node->start;
            $nextPos += $length;
            $totalLength += $length;
            if ($node->isTransient) {
                continue;
            }
            $children[] = $node;
        }

        return new Node($this->name, $pos, $pos + $totalLength, null, $children);
    }
}
