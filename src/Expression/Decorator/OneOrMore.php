<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Expression\Decorator;

use ju1ius\Pegasus\Expression;

/**
 * An expression wrapper like the + quantifier in regexes.
 */
final class OneOrMore extends Quantifier
{
    public function __construct(?Expression $child = null, $name = '')
    {
        parent::__construct($child, 1, null, $name);
    }

    /**
     * @inheritDoc
     */
    public function isZeroOrMore(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function isOneOrMore(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function isOptional(): bool
    {
        return false;
    }
}
