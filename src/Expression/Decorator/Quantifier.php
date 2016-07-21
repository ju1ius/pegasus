<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Expression\Decorator;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Decorator;
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
    protected $lowerBound;

    /**
     * @var int
     */
    protected $upperBound;

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
        $this->lowerBound = abs((int)$min);
        if ($max < $min) {
            throw new \InvalidArgumentException('upper bound must be >= lower bound');
        }
        $this->upperBound = $max === INF ? $max : (int)$max;

        parent::__construct($child, $name);
    }

    /**
     * @return int
     */
    public function getLowerBound()
    {
        return $this->lowerBound;
    }

    /**
     * @return int
     */
    public function getUpperBound()
    {
        return $this->upperBound;
    }

    /**
     * Returns whether the upper bound is infinite.
     *
     * @return bool
     */
    public function isUnbounded()
    {
        return $this->upperBound === INF;
    }

    /**
     * @return bool
     */
    public function isExact()
    {
        return $this->lowerBound === $this->upperBound;
    }

    /**
     * @return bool
     */
    public function isZeroOrMore()
    {
        return $this->lowerBound === 0 && $this->upperBound === INF;
    }

    /**
     * @return bool
     */
    public function isOneOrMore()
    {
        return $this->lowerBound === 1 && $this->upperBound === INF;
    }

    /**
     * @return bool
     */
    public function isOptional()
    {
        return $this->lowerBound === 0 && $this->upperBound === 1;
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
        } elseif ($this->lowerBound === $this->upperBound) {
            $q = sprintf('{%d}', $this->lowerBound);
        } else {
            $q = sprintf('{%d,%s}', $this->lowerBound, $this->isUnbounded() ? '' : $this->upperBound);
        }

        return $this->stringChildren()[0] . $q;
    }

    public function match($text, Parser $parser, Scope $scope)
    {
        $expr = $this->children[0];
        $startPos = $parser->pos;
        $capturing = $parser->isCapturing;
        $matchCount = 0;
        $results = $capturing ? [] : null;
        while ($result = $expr->match($text, $parser, $scope)) {
            if ($capturing) {
                $results[] = $result;
            }
            if (++$matchCount === $this->upperBound) {
                break;
            }
        }
        if ($matchCount >= $this->lowerBound) {
            return $capturing
                ? new Node\Quantifier($this->name, $startPos, $parser->pos, $results, $this->isOptional())
                : true;
        }
        $parser->pos = $startPos;
    }
}
