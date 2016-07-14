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
use ju1ius\Pegasus\Parser\Parser;
use ju1ius\Pegasus\Parser\Scope;

/**
 * A series of expressions that must match contiguous, ordered pieces of the text.
 *
 * In other words, it's a concatenation operator: each piece has to match, one after another.
 */
class Sequence extends Combinator
{
    public function getCaptureCount()
    {
        return array_reduce($this->children, function ($n, Expression $child) {
            return $child->isCapturing() ? $n + 1 : $n;
        }, 0);
    }

    public function match($text, Parser $parser, Scope $scope)
    {
        $startPos = $parser->pos;
        $capturing = $parser->isCapturing;
        $children = $capturing ? [] : null;
        foreach ($this->children as $child) {
            $result = $child->match($text, $parser, $scope);
            if (!$result) {
                $parser->pos = $startPos;
                return null;
            }
            if ($result === true || !$capturing) {
                continue;
            }
            $children[] = $result;
        }

        return $capturing
            ? new Node\Composite($this->name, $startPos, $parser->pos, $children)
            : true;
    }

    /**
     * @inheritDoc
     */
    protected function stringChildren()
    {
        return array_map(function (Expression $child) {
            if ($child instanceof OneOf || $child instanceof NamedSequence) {
                return sprintf('(%s)', $child);
            }

            return (string)$child;
        }, $this->children);
    }

    public function __toString()
    {
        return implode(' ', $this->stringChildren());
    }
}
