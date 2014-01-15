<?php

namespace ju1ius\Pegasus\Visitor;

use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Expression;


/**
 * Generic GrammarVisitorInterface implementation.
 *
 * @codeCoverageIgnore
 */
class GrammarVisitor implements GrammarVisitorInterface
{
    /**
     * {@inheritDoc}
     */
    public function beforeTraverse(Grammar $grammar)
    {
        return null;
    }
    /**
     * {@inheritDoc}
     */
    public function afterTraverse(Grammar $grammar)
    {
        return null;
    }
    /**
     * {@inheritDoc}
     */
    public function enterRule(Grammar $grammar, Expression $expr)
    {
        return null;
    }
    /**
     * {@inheritDoc}
     */
    public function leaveRule(Grammar $grammar, Expression $expr)
    {
        return null;
    }
    /**
     * {@inheritDoc}
     */
    public function enterExpression(Grammar $grammar, Expression $expr)
    {
        return null;
    }
    /**
     * {@inheritDoc}
     */
    public function leaveExpression(Grammar $grammar, Expression $expr)
    {
        return null;
    }
}
