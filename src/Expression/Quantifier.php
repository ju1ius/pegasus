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
use ju1ius\Pegasus\Parser\Parser;
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
        if ($this->isZeroOrMore()) {
            $q = '*';
        } elseif ($this->isOneOrMore()) {
            $q = '+';
        } elseif ($this->isOptional()) {
            $q = '?';
        } else {
            $q = sprintf('{%s,%s}', $this->min, $this->max === INF ? '' : $this->max);
        }

        return $this->stringChildren()[0] . $q;
    }

    public function match($text, Parser $parser, Scope $scope)
    {
        $expr = $this->children[0];
        $startPos = $parser->pos;
        $results = [];
        $matchCount = 0;
        while ($result = $expr->match($text, $parser, $scope)) {
            $results[] = $result;
            if (++$matchCount === $this->max) {
                break;
            }
        }
        if ($matchCount >= $this->min) {
            return $parser->isCapturing
                ? new Node\Quantifier($this->name, $startPos, $parser->pos, $results, $this->isOptional())
                : true;
        }
        $parser->pos = $startPos;
    }
}
