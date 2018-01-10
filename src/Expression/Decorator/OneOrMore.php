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
use ju1ius\Pegasus\Expression\Decorator\Quantifier;

/**
 * An expression wrapper like the + quantifier in regexes.
 *
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
