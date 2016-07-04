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
        $new_pos = $pos;
        $seq_len = 0;
        $children = [];
        foreach ($this->children as $child) {
            $node = $parser->apply($child, $new_pos, $scope);
            if (!$node) {
                return null;
            }
            $children[] = $node;
            $len = $node->end - $node->start;
            $new_pos += $len;
            $seq_len += $len;
        }

        return new Node($this->name, $pos, $pos + $seq_len, null, $children);
    }

    public function parse($text, $pos, $rules, Scope $scope)
    {
        $newPos = $pos;
        $totalLength = 0;
        $children = [];
        $childrenScope = $scope->nest();
        foreach ($this->children as $child) {
            $result = $child->parse($text, $newPos, $rules, $childrenScope); // {|_| scope = scope.merge _ } ???
            if (!$result) {
                return null;
            }
            $childrenScope = $childrenScope->capture($result);
            $children[] = $result;
            //$length = $result->end - $result->start;
            $length = strlen($result);
            $newPos += $length;
            $totalLength += $length;
        }

        return $children;
    }
}
