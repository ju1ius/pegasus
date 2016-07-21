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

/**
 * An expression wrapper like the * quantifier in regexes.
 *
 */
class ZeroOrMore extends Quantifier
{
    public function __construct(Expression $child = null, $name = '')
    {
        parent::__construct($child, 0, INF, $name);
    }

    /**
     * @inheritDoc
     */
    public function isZeroOrMore()
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function isOneOrMore()
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function isOptional()
    {
        return false;
    }
}
