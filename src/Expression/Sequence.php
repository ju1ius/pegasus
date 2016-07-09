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

use ju1ius\Pegasus\Expression;
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
        return implode(' ', $this->stringChildren());
    }

    public function getCaptureCount()
    {
        return array_reduce($this->children, function ($n, Expression $child) {
            return $child->isCapturing() ? $n + 1 : $n;
        }, 0);
    }

    public function match($text, $pos, ParserInterface $parser, Scope $scope)
    {
        $nextPos = $pos;
        $children = [];
        foreach ($this->children as $child) {
            $node = $parser->apply($child, $nextPos, $scope);
            if (!$node) {
                return null;
            }
            $length = $node->end - $node->start;
            $nextPos += $length;
            if ($node->isTransient) {
                continue;
            }
            $children[] = $node;
        }

        return new Node\Composite($this->name, $pos, $nextPos, $children);
    }
}
