<?php

namespace ju1ius\Pegasus\Visitor;

use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Expression;


interface GrammarVisitorInterface
{
    /**
     * Called once before traversal.
     *
     * Return value semantics:
     *  * null:      $grammar stays as-is
     *  * otherwise: $grammar is set to the return value
     *
     * @param ju1ius\Pegasus\Grammar $grammar
     *
     * @return mixed
     */
    public function beforeTraverse(Grammar $grammar);

    /**
     * Called once after traversal.
     *
     * Return value semantics:
     *  * null:      $grammar stays as-is
     *  * otherwise: $grammar is set to the return value
     *
     * @param ju1ius\Pegasus\Grammar $grammar
     *
     * @return mixed
     */
    public function afterTraverse(Grammar $grammar);

    /**
     * Called when entering a grammar rule.
     *
     * Return value semantics:
     *  * null:      $expr stays as-is
     *  * otherwise: $expr is set to the return value
     *
     * @param ju1ius\Pegasus\Grammar    $grammar    The visited grammar. 
     * @param ju1ius\Pegasus\Expression $expr       The visited expression. 
     *
     * @return mixed
     */
    public function enterRule(Grammar $grammar, Expression $expr);

    /**
     * Called when leaving a grammar rule.
     *
     * Return value semantics:
     *  * null:      $expr stays as-is
     *  * false:     $expr is removed from the grammar. 
     *  * array:     The return value is merged into the parent array (at the position of the $node)
     *  * otherwise: $expr is set to the return value
     *
     * @param ju1ius\Pegasus\Grammar    $grammar    The visited grammar. 
     * @param ju1ius\Pegasus\Expression $expr       The visited expression. 
     *
     * @return mixed
     */
    public function leaveRule(Grammar $grammar, Expression $expr);

    /**
     * Called when entering an expression.
     *
     * Return value semantics:
     *  * null:      $expr stays as-is
     *  * otherwise: $expr is set to the return value
     *
     * @param ju1ius\Pegasus\Grammar    $grammar    The visited grammar. 
     * @param ju1ius\Pegasus\Expression $expr       The visited expression. 
     *
     * @return mixed
     */
    public function enterExpression(Grammar $grammar, Expression $expr);

    /**
     * Called when leaving an expression.
     *
     * Return value semantics:
     *  * null:      $expr stays as-is
     *  * false:     $expr is removed from the parent array
     *  * array:     The return value is merged into the parent array (at the position of the $node)
     *  * otherwise: $expr is set to the return value
     *
     * @param ju1ius\Pegasus\Grammar    $grammar    The visited grammar. 
     * @param ju1ius\Pegasus\Expression $expr       The visited expression. 
     *
     * @return mixed
     */
    public function leaveExpression(Grammar $grammar, Expression $expr);
}
