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
use ju1ius\Pegasus\Visitor\ExpressionVisitorInterface;

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
     * @param Expression $expr The expression to traverse
     *
     * @return mixed The result of the traversal.
     */
    public function traverse(Expression $expr);
}
