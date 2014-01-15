<?php

namespace ju1ius\Pegasus\Visitor;

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
     * @param ju1ius\Pegasus\Expression $expr
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
     * @param ju1ius\Pegasus\Expression $expr The visited expression 
     *
     * @return mixed
     */
    public function enterExpression(Expression $expr);

    /**
     * Called when leaving a node.
     *
     * Return value semantics:
     *  * null:      $node stays as-is
     *  * false:     $node is removed from the parent array
     *  * array:     The return value is merged into the parent array (at the position of the $node)
     *  * otherwise: $node is set to the return value
     *
     * @param ju1ius\Pegasus\Expression $expr The visited expression
     *
     * @return mixed
     */
    public function leaveExpression(Expression $expr);

    /**
     * Called once after traversal.
     *
     * Return value semantics:
     *  * null:      $nodes stays as-is
     *  * otherwise: $nodes is set to the return value
     *
     * @param ju1ius\Pegasus\Expression $expr The visited expression 
     *
     * @return mixed
     */
    public function afterTraverse(Expression $expr);
}
