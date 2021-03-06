<?php declare(strict_types=1);
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

/**
 * An expression wrapper like the * quantifier in regexes.
 *
 */
final class ZeroOrMore extends Quantifier
{
    public function __construct(?Expression $child = null, string $name = '')
    {
        parent::__construct($child, 0, null, $name);
    }

    /**
     * @inheritDoc
     */
    public function isZeroOrMore(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function isOneOrMore(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function isOptional(): bool
    {
        return false;
    }
}
