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

/**
 * A composite expression which contains only one sub-expression.
 * ATM it does nothing more than Composite, and is here only for easier type-checking in visitors.
 *
 */
abstract class Decorator extends Composite
{
    /**
     * @param Expression $child
     * @param string     $name
     */
    public function __construct(Expression $child = null, $name = '')
    {
        parent::__construct($child ? [$child] : [], $name);
    }

    public function stringChildren()
    {
        return parent::stringChildren()[0];
    }

    public function offsetSet($offset, $value)
    {
        if ($offset === 0 || ($offset === null && $this->count() === 0)) {
            return parent::offsetSet(0, $value);
        }

        throw new \OverflowException(sprintf(
            '`%s` instances accepts only a single child.',
            get_class($this)
        ));
    }
}
