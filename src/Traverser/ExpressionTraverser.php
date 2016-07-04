<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Traverser;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Composite;
use ju1ius\Pegasus\Visitor\ExpressionVisitor;
use ju1ius\Pegasus\Visitor\ExpressionVisitorInterface;

class ExpressionTraverser implements ExpressionTraverserInterface
{
    /**
     * @var \SplObjectStorage.<ExpressionVisitor>
     */
    protected $visitors;

    /**
     * Keeps track of the traversed expressions to avoid infinite recursion with recursive rules.
     *
     * @var array
     */
    protected $visited = [];

    public function __construct()
    {
        $this->visitors = new \SplObjectStorage();
    }

    /**
     * @inheritDoc
     */
    public function addVisitor(ExpressionVisitorInterface ...$visitors)
    {
        foreach ($visitors as $visitor) {
            $this->visitors->attach($visitor);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function removeVisitor(ExpressionVisitorInterface ...$visitors)
    {
        foreach ($visitors as $visitor) {
            $this->visitors->detach($visitor);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function traverse(Expression $expr)
    {
        $this->visited = [];

        foreach ($this->visitors as $visitor) {
            if (null !== $result = $visitor->beforeTraverse($expr)) {
                $expr = $result;
            }
        }

        if (null !== $result = $this->traverseExpression($expr)) {
            $expr = $result;
        }

        foreach ($this->visitors as $visitor) {
            if (null !== $result = $visitor->afterTraverse($expr)) {
                $expr = $result;
            }
        }

        return $expr;
    }

    protected function traverseExpression(Expression $expr)
    {
        foreach ($this->visitors as $visitor) {
            if (null !== $result = $visitor->enterExpression($expr)) {
                $expr = $result;
            }
        }

        if ($expr instanceof Composite) {
            foreach ($expr as $i => $child) {
                // protect against recursive rules
                if (isset($this->visited[$child->id])) {
                    continue;
                }
                $this->visited[$child->id] = true;

                if (null !== $result = $this->traverseExpression($child)) {
                    $expr[$i] = $result;
                }
            }
        }

        foreach ($this->visitors as $visitor) {
            if (null !== $result = $visitor->leaveExpression($expr)) {
                $expr = $result;
            }
        }

        return $expr;
    }
}
