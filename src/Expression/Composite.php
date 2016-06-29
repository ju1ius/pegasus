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
 * An expression which contains several other expressions.
 *
 * @author ju1ius <ju1ius@laposte.net>
 */
abstract class Composite extends Expression
{
    /**
     * Holds an array of this expression's sub expressions.
     *
     * @var Expression[]
     */
    public $children;

    /**
     * Composite constructor.
     *
     * Subclasses MUST always respect this constructor parameter order.
     *
     * @param Expression[]  $children
     * @param string $name
     */
    public function __construct(array $children = [], $name = '')
    {
        parent::__construct($name);
        $this->children = array_values($children);
    }

    /**
     * @inheritdoc
     */
    public function equals(Expression $other)
    {
        if (!parent::equals($other)) {
            return false;
        }
        foreach ($this->children as $i => $child) {
            /** @var Composite $other */
            if (!isset($other->children[$i]) || !$child->equals($other->children[$i])) {
                return false;
            }
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function isCapturing()
    {
        foreach ($this->children as $child) {
            if ($child->isCapturing()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function isCapturingDecidable()
    {
        foreach ($this->children as $child) {
            if (!$child->isCapturingDecidable()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Return an of string represented children,
     * stopping descent when we hit a named node so the returned value
     * resembles the input rule.
     *
     */
    protected function stringMembers()
    {
        return array_map(function(Expression $child) {
            if ($child instanceof Reference) {
                return $child->asRightHandSide();
            }
            return $child->name ?: $child->asRightHandSide();
        }, $this->children);
    }
}
