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
use ju1ius\Pegasus\Node;
use ju1ius\Pegasus\Parser\ParserInterface;
use ju1ius\Pegasus\Parser\Scope;

/**
 * An expression wrapper like the {n, n+i} quantifier in regular expressions.
 */
class Quantifier extends Decorator
{
    /**
     * @var int
     */
    public $min;

    /**
     * @var int
     */
    public $max;

    /**
     * Quantifier constructor.
     *
     * @param Expression|null $child
     * @param int             $min
     * @param int             $max
     * @param string          $name
     */
    public function __construct(Expression $child = null, $min, $max, $name = '')
    {
        $this->min = abs((int)$min);
        if ($max < $min) {
            throw new \InvalidArgumentException('$max must be >= $min');
        }
        $this->max = $max === INF ? $max : (int)$max;

        parent::__construct($child, $name);
    }

    /**
     * @return bool
     */
    public function isZeroOrMore()
    {
        return $this->min === 0 && $this->max === INF;
    }

    /**
     * @return bool
     */
    public function isOneOrMore()
    {
        return $this->min === 1 && $this->max === INF;
    }

    /**
     * @return bool
     */
    public function isOptional()
    {
        return $this->min === 0 && $this->max === 1;
    }

    public function hasVariableCaptureCount()
    {
        return true;
    }

    public function __toString()
    {
        return sprintf(
            '(%s){%s,%s}',
            $this->stringChildren(),
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
            return new Node\Quantifier($this->name, $pos, $nextPosition, $children, $this->isOptional());
        }
    }
}
