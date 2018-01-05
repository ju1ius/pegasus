<?php declare(strict_types=1);
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

interface ExpressionVisitorInterface
{
    /**
     * Called once before traversal.
     *
     * Return value semantics:
     *  * null:      $nodes stays as-is
     *  * otherwise: $nodes is set to the return value
     *
     * @param Expression $expr
     *
     * @return mixed
     */
    public function beforeTraverse(Expression $expr);

    /**
     * Called when entering a node.
     *
     * Return value semantics:
     *  * null:      $node stays as-is
     *  * otherwise: $node is set to the return value
     *
     * @param Expression $expr The visited expression
     * @param int|null $index The index of the visited expression in it's parent (null inside a top-level expression).
     * @param bool $isLast Whether the visited expression is the last child of it's parent
     *
     * @return mixed
     */
    public function enterExpression(Expression $expr, ?int $index = null, bool $isLast = false);

    /**
     * Called when leaving a node.
     *
     * Return value semantics:
     *  * null:      $node stays as-is
     *  * false:     $node is removed from the parent array
     *  * array:     The return value is merged into the parent array (at the position of the $node)
     *  * otherwise: $node is set to the return value
     *
     * @param Expression $expr The visited expression
     * @param int|null $index The index of the visited expression in it's parent (null inside a top-level expression).
     * @param bool $isLast Whether the visited expression is the last child of it's parent
     *
     * @return mixed
     */
    public function leaveExpression(Expression $expr, ?int $index = null, bool $isLast = false);

    /**
     * Called once after traversal.
     *
     * Return value semantics:
     *  * null:      $nodes stays as-is
     *  * otherwise: $nodes is set to the return value
     *
     * @param Expression $expr The visited expression
     *
     * @return mixed
     */
    public function afterTraverse(Expression $expr);
}
