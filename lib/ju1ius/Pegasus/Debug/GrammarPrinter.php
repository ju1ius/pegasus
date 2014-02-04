<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable 
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace ju1ius\Pegasus\Debug;

use ju1ius\Pegasus\Visitor\GrammarVisitor;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Expression;


/**
 * Prints a grammar's rules & expression tree to stdout.
 *
 */
class GrammarPrinter extends GrammarVisitor
{
    public function enterRule(Grammar $grammar, Expression $expr)
    {
        $this->level = 1;
        echo $this->formatRule($expr);
    }

    public function enterExpression(Grammar $grammar, Expression $expr)
    {
        echo $this->formatExpression($expr);
        if ($expr instanceof Expression\Composite) {
            $this->level++;
        }
    }

    public function leaveExpression(Grammar $grammar, Expression $expr)
    {
        if ($expr instanceof Expression\Composite) {
            $this->level--;
        }
    }

    protected function formatRule(Expression $expr)
    {
        return $expr->asRule() . "\n";
    }

    protected function formatExpression(Expression $expr)
    {
        return sprintf(
            "%s+<%s(%s) = %s>\n",
            $this->indent(),
            $expr->name ?: '',
            str_replace('ju1ius\Pegasus\Expression\\', '', get_class($expr)),
            $expr->asRhs()
        );
    }

    protected function indent()
    {
        return str_repeat('+---', $this->level);
    }
}
