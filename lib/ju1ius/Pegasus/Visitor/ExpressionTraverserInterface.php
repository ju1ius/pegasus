<?php

namespace ju1ius\Pegasus\Visitor;

use ju1ius\Pegasus\Expression;


interface ExpressionTraverserInterface
{
    /**
     * Adds a visitor.
     *
     * @param ExpressionVisitorInterface $visitor Visitor to add
	 *
	 * @return $this
     */
    public function addVisitor(ExpressionVisitorInterface $visitor);

    /**
     * Removes an added visitor.
     *
     * @param ExpressionVisitorInterface $visitor
	 *
	 * @return $this
     */
    public function removeVisitor(ExpressionVisitorInterface $visitor);

    /**
     * Traverses an expression using the registered visitors.
     *
     * @param ju1ius\Pegasus\Expression $expr The expression to traverse
     *
     * @return mixed The result of the traversal.
     */
    public function traverse(Expression $expr);
}
