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
 * ATM it does nothing more than Composite, and is here only for easier type-checking in optimizations.
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

    public function isCapturing()
    {
        return $this->children[0]->isCapturing();
    }

    public function isCapturingDecidable()
    {
        return $this->children[0]->isCapturingDecidable();
    }

    /**
     * @inheritdoc
     */
    public function stringChildren()
    {
        $child = $this->children[0];
        $str = $child instanceof Composite ? sprintf('(%s)', $child) : (string)$child;

        return [$str];
    }

    /**
     * @param int|null   $offset
     * @param Expression $value
     *
     * @return Expression
     *
     * @throws \OverflowException when offset is not 0 or when trying to append a child when there's already one.
     */
    public function offsetSet($offset, $value)
    {
        if ($offset === 0 || ($offset === null && $this->count() === 0)) {
            return parent::offsetSet(0, $value);
        }

        throw new \OverflowException(sprintf(
            '`%s` expressions accept only a single child.',
            get_class($this)
        ));
    }
}
