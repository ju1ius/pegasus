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

use ju1ius\Pegasus\Parser\ParserInterface;
use ju1ius\Pegasus\Node;


/**
 * An expression wrapper like the {n, n+i} quantifier in regexes
 */
class Quantifier extends Wrapper
{
    public $min;
    public $max;

    public function __construct(array $children, $min, $max, $name='')
    {
        $this->min = abs((int) $min);
        if ($max < $min) {
            throw new \InvalidArgumentException('$max must be >= $min');
        }
        $this->max = $max === INF ? $max : (int) $max;

        parent::__construct($children, $name);    
    }

    public function hasVariableCaptureCount()
    {
        return true;
    }

    public function asRhs()
    {
        return sprintf(
            '(%s){%s,%s}',
            $this->stringMembers(),
            $this->min,
            $this->max === INF ? '' : $this->max
        );
    }

    public function match($text, $pos, ParserInterface $parser)
    {
        $new_pos = $pos;
        $children = [];
        $match_count = 0;
        while (true) {
            $node = $parser->apply($this->children[0], $new_pos);
            if (!$node) break;
            $match_count++;

            $children[] = $node;
            $length = $node->end - $node->start;
            if (!$length) break;

            $new_pos += $length;

            if ($match_count === $this->max) break;
        }
        if ($match_count >= $this->min) {
            return new Node\Quantifier($this, $text, $pos, $new_pos, $children);
        }
    }
}
