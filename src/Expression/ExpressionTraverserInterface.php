<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Expression;

use ju1ius\Pegasus\Expression;

interface ExpressionTraverserInterface
{
    /**
     * Adds a visitor.
     *
     * @param ExpressionVisitorInterface ...$visitors Visitor to add
     *
     * @return $this
     */
    public function addVisitor(ExpressionVisitorInterface ...$visitors): static;

    /**
     * Removes an added visitor.
     *
     * @param ExpressionVisitorInterface ...$visitors
     *
     * @return $this
     */
    public function removeVisitor(ExpressionVisitorInterface ...$visitors): static;

    /**
     * Traverses an expression using the registered optimizations.
     *
     * @param Expression $expr The expression to traverse
     *
     * @return mixed The result of the traversal.
     */
    public function traverse(Expression $expr): mixed;
}
