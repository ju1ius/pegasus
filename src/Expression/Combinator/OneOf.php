<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Expression\Combinator;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Combinator;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\Parser\Parser;
use ju1ius\Pegasus\Parser\Scope;

/**
 * A series of expressions, one of which must match
 *
 * Expressions are tested in order from first to last.
 * The first to succeed wins.
 */
class OneOf extends Combinator
{
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
        $capturing = $parser->isCapturing;
        foreach ($this->children as $child) {
            if ($result = $child->match($text, $parser, $scope)) {
                if ($result === true || !$capturing) {
                    return true;
                }
                // Tree decimation:
                // Try to skip one tree level if either this expression or it's matching child is not a grammar rule
                if (!$this->name) {
                    return $result;
                } elseif (!$result->name) {
                    $result->name = $this->name;

                    return $result;
                }

                return new Node\Decorator($this->name, $start, $parser->pos, $result);
            }
            $parser->pos = $start;
        }
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        return implode(' | ', $this->stringChildren());
    }

    /**
     * @inheritDoc
     */
    protected function stringChildren()
    {
        return array_map(function (Expression $child) {
            return $child instanceof OneOf ? sprintf('(%s)', $child) : (string)$child;
        }, $this->children);
    }
}
