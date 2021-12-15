<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Expression\Decorator;

use ju1ius\Pegasus\CST\Node\Quantifier as QuantifierNode;
use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Decorator;
use ju1ius\Pegasus\Parser\Parser;

/**
 * An expression wrapper like the {n, n+i} quantifier in regular expressions.
 */
class Quantifier extends Decorator
{
    protected int $lowerBound;
    protected int|float $upperBound;

    public function __construct(?Expression $child, int $min, ?int $max = null, string $name = '')
    {
        $this->lowerBound = abs($min);
        $this->upperBound = $max === null ? INF : $max;
        if ($this->upperBound < $this->lowerBound) {
            throw new \InvalidArgumentException('upper bound must be >= lower bound');
        }

        parent::__construct($child, $name);
    }

    public function getLowerBound(): int
    {
        return $this->lowerBound;
    }

    public function getUpperBound(): int|float
    {
        return $this->upperBound;
    }

    /**
     * Returns whether the upper bound is infinite.
     */
    public function isUnbounded(): bool
    {
        return $this->upperBound === INF;
    }

    public function isExact(): bool
    {
        return $this->lowerBound === $this->upperBound;
    }

    public function isZeroOrMore(): bool
    {
        return $this->lowerBound === 0 && $this->upperBound === INF;
    }

    public function isOneOrMore(): bool
    {
        return $this->lowerBound === 1 && $this->upperBound === INF;
    }

    public function isOptional(): bool
    {
        return $this->lowerBound === 0 && $this->upperBound === 1;
    }

    public function hasVariableCaptureCount(): bool
    {
        return true;
    }

    public function __toString(): string
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

        return sprintf(
            '(%s)%s',
            implode('', $this->stringChildren()),
            $q
        );
    }

    public function matches(string $text, Parser $parser): QuantifierNode|bool
    {
        $expr = $this->children[0];
        $startPos = $parser->pos;
        $capturing = $parser->isCapturing;
        $matchCount = 0;
        $results = $capturing ? [] : null;
        while ($result = $expr->matches($text, $parser)) {
            if ($capturing && !\is_bool($result)) {
                $results[] = $result;
            }
            if (++$matchCount === $this->upperBound) {
                break;
            }
        }
        if ($matchCount >= $this->lowerBound) {
            return $capturing
                ? new QuantifierNode($this->name, $startPos, $parser->pos, $results, $this->isOptional())
                : true;
        }
        $parser->pos = $startPos;
        return false;
    }
}
