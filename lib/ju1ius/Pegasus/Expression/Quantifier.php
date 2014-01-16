<?php

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

    public function __construct(array $members, $name='', $min=0, $max=null)
    {
        $this->min = abs((int) $min);
        if (null === $max) {
            $max = -1;
        } else if ($max < $min) {
            throw new \InvalidArgumentException('$max must be null or > $min');
        }
        $this->max = (int) $max;

        parent::__construct($members, $name);    
    }

    public function hasVariableCaptureCount()
    {
        return true;
    }

    public function asRhs()
    {
        return sprintf(
            '(%s){%s%s}',
            $this->stringMembers(),
            $this->min,
            $this->max === -1 ? '' : ','.$this->max
        );
    }

    public function match($text, $pos, ParserInterface $parser)
    {
        $new_pos = $pos;
        $children = [];
        $match_count = 0;
        while (true) {
            $node = $parser->apply($this->members[0], $new_pos);
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
