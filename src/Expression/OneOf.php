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
use ju1ius\Pegasus\Parser\Parser;
use ju1ius\Pegasus\Parser\Scope;

/**
 * A series of expressions, one of which must match
 *
 * Expressions are tested in order from first to last.
 * The first to succeed wins.
 */
class OneOf extends Composite
{
    public function __toString()
    {
        return implode(' | ', $this->stringChildren());
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

    public function match($text, Parser $parser, Scope $scope)
    {
        $start = $parser->pos;
        foreach ($this->children as $child) {
            if ($node = $child->match($text, $parser, $scope)) {
                return new Node\Decorator($this->name, $start, $parser->pos, $node);
            }
            $parser->pos = $start;
        }
    }
}
