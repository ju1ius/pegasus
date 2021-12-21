<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Grammar;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Grammar;

interface GrammarVisitorInterface
{
    /**
     * Called once before traversal.
     *
     * Return value semantics:
     *  * null:      $grammar stays as-is
     *  * otherwise: $grammar is set to the return value
     */
    public function beforeTraverse(Grammar $grammar): ?Grammar;

    /**
     * Called once after traversal.
     *
     * Return value semantics:
     *  * null:      $grammar stays as-is
     *  * otherwise: $grammar is set to the return value
     */
    public function afterTraverse(Grammar $grammar): ?Grammar;

    /**
     * Called when entering a grammar rule.
     *
     * Return value semantics:
     *  * null:      $expr stays as-is
     *  * otherwise: $expr is set to the return value
     */
    public function enterRule(Grammar $grammar, Expression $expr): ?Expression;

    /**
     * Called when leaving a grammar rule.
     *
     * @todo the following semantics are not implemented by the abstract grammar traverser class !
     *
     * Return value semantics:
     *  * null:      $expr stays as-is
     *  * false:     $expr is removed from the grammar.
     *  * array:     The return value is merged into the parent array (at the position of the $node)
     *  * otherwise: $expr is set to the return value
     */
    public function leaveRule(Grammar $grammar, Expression $expr): ?Expression;

    /**
     * Called when entering an expression.
     *
     * Return value semantics:
     *  * null:      $expr stays as-is
     *  * otherwise: $expr is set to the return value
     *
     * @param Expression $expr The visited expression.
     * @param int|null $index The index of the visited expression in its parent.
     * @param bool $isLast Whether the visited expression is the last child of it's parent
     */
    public function enterExpression(Expression $expr, ?int $index = null, bool $isLast = false): ?Expression;

    /**
     * Called when leaving an expression.
     *
     * @todo the following semantics are not implemented by the abstract grammar traverser class !
     *
     * Return value semantics:
     *  * null:      $expr stays as-is
     *  * false:     $expr is removed from the parent array
     *  * array:     The return value is merged into the parent array (at the position of the $node)
     *  * otherwise: $expr is set to the return value
     *
     * @param Expression $expr The visited expression.
     * @param int|null $index The index of the visited expression in its parent.
     * @param bool $isLast Whether the visited expression is the last child of it's parent
     */
    public function leaveExpression(Expression $expr, ?int $index = null, bool $isLast = false): ?Expression;
}
