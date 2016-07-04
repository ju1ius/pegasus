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
use ju1ius\Pegasus\Parser\Scope;

/**
 * An expression wrapper like the {n, n+i} quantifier in regular expressions.
 */
class Quantifier extends Decorator
{
    public $min;
    public $max;

    public function __construct(array $children, $min, $max, $name = '')
    {
        $this->min = abs((int)$min);
        if ($max < $min) {
            throw new \InvalidArgumentException('$max must be >= $min');
        }
        $this->max = $max === INF ? $max : (int)$max;

        parent::__construct($children, $name);
    }

    public function hasVariableCaptureCount()
    {
        return true;
    }

    public function __toString()
    {
        return sprintf(
            '(%s){%s,%s}',
            $this->stringMembers(),
            $this->min,
            $this->max === INF ? '' : $this->max
        );
    }

    public function match($text, $pos, ParserInterface $parser, Scope $scope)
    {
        $nextPosition = $pos;
        $children = [];
        $matchCount = 0;
        while (true) {
            $node = $parser->apply($this->children[0], $nextPosition, $scope);
            if (!$node) {
                break;
            }
            $matchCount++;

            $children[] = $node;
            $length = $node->end - $node->start;
            if (!$length) {
                break;
            }

            $nextPosition += $length;

            if ($matchCount === $this->max) {
                break;
            }
        }

        if ($matchCount >= $this->min) {
            return new Node($this->name, $pos, $nextPosition, $text, $children);
        }
    }
}
