<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable 
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


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
