<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Expression\Decorator;

use ju1ius\Pegasus\Expression;

/**
 * An expression wrapper like the * quantifier in regexes.
 */
final class ZeroOrMore extends Quantifier
{
    public function __construct(?Expression $child = null, string $name = '')
    {
        parent::__construct($child, 0, null, $name);
    }

    public function isZeroOrMore(): bool
    {
        return true;
    }

    public function isOneOrMore(): bool
    {
        return false;
    }

    public function isOptional(): bool
    {
        return false;
    }
}
