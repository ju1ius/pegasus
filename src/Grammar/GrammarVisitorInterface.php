<?php declare(strict_types=1);
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
     *
     * @param Grammar $grammar
     *
     * @return mixed
     */
    public function beforeTraverse(Grammar $grammar): ?Grammar;

    /**
     * Called once after traversal.
     *
     * Return value semantics:
     *  * null:      $grammar stays as-is
     *  * otherwise: $grammar is set to the return value
     *
     * @param Grammar $grammar
     *
     * @return mixed
     */
    public function afterTraverse(Grammar $grammar): ?Grammar;

    /**
     * Called when entering a grammar rule.
     *
     * Return value semantics:
     *  * null:      $expr stays as-is
     *  * otherwise: $expr is set to the return value
     *
     * @param Grammar    $grammar The visited grammar.
     * @param Expression $expr    The visited expression.
     *
     * @return mixed
     */
    public function enterRule(Grammar $grammar, Expression $expr);

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
     *
     * @param Grammar    $grammar The visited grammar.
     * @param Expression $expr    The visited expression.
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
     * @param Expression $expr The visited expression.
     * @param int|null $index The index of the visited expression in it's parent.
     * @param bool $isLast Whether the visited expression is the last child of it's parent
     *
     * @return mixed
     */
    public function enterExpression(Expression $expr, ?int $index = null, bool $isLast = false);

    /**
     * Called when leaving an expression.
     *
     * Return value semantics:
     *  * null:      $expr stays as-is
     *  * false:     $expr is removed from the parent array
     *  * array:     The return value is merged into the parent array (at the position of the $node)
     *  * otherwise: $expr is set to the return value
     *
     * @param Expression $expr The visited expression.
     * @param int|null $index The index of the visited expression in it's parent.
     * @param bool $isLast Whether the visited expression is the last child of it's parent
     *
     * @return mixed
     */
    public function leaveExpression(Expression $expr, ?int $index = null, bool $isLast = false);
}
