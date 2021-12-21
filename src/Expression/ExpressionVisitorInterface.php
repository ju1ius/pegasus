<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Expression;

use ju1ius\Pegasus\Expression;

interface ExpressionVisitorInterface
{
    /**
     * Called once before traversal.
     *
     * Return value semantics:
     *  * null:      $expr stays as-is
     *  * otherwise: $expr is set to the return value
     */
    public function beforeTraverse(Expression $expr): ?Expression;

    /**
     * Called once after traversal.
     *
     * Return value semantics:
     *  * null:      $expr stays as-is
     *  * otherwise: $expr is set to the return value
     *
     * @param Expression $expr The visited expression
     *
     * @return Expression|null
     */
    public function afterTraverse(Expression $expr): ?Expression;

    /**
     * Called when entering a node.
     *
     * Return value semantics:
     *  * null:      $expr stays as-is
     *  * otherwise: $expr is set to the return value
     *
     * @param Expression $expr The visited expression
     * @param int|null $index The index of the visited expression in it's parent (null inside a top-level expression).
     * @param bool $isLast Whether the visited expression is the last child of it's parent
     */
    public function enterExpression(Expression $expr, ?int $index = null, bool $isLast = false): ?Expression;

    /**
     * Called when leaving a node.
     *
     * Return value semantics:
     *  * null:      $expr stays as-is
     *  * false:     $expr is removed from the parent array
     *  * array:     The return value is merged into the parent array (at the position of the $expr)
     *  * otherwise: $expr is set to the return value
     *
     * @param Expression $expr The visited expression
     * @param int|null $index The index of the visited expression in it's parent (null inside a top-level expression).
     * @param bool $isLast Whether the visited expression is the last child of it's parent
     */
    public function leaveExpression(Expression $expr, ?int $index = null, bool $isLast = false): ?Expression;
}
