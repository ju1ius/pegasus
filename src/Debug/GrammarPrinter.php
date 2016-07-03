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

use ju1ius\Pegasus\Expression\Composite;
use ju1ius\Pegasus\Visitor\GrammarVisitor;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Expression;


/**
 * Prints a grammar's rules & expression tree to stdout.
 *
 */
final class GrammarPrinter extends GrammarVisitor
{
    /**
     * @var int
     */
    private $depth = 0;

    public function enterRule(Grammar $grammar, Expression $expr)
    {
        $this->depth = 0;
        echo sprintf("%s <- %s\n", $expr->name, $expr);
    }

    public function enterExpression(Grammar $grammar, Expression $expr)
    {
        if ($expr instanceof Composite) {
            $this->depth++;
        }
        $indent = str_repeat('│ ', $this->depth - 1);
        $indent .= $expr instanceof Composite ? '├ ' : '└ ';
        echo sprintf(
            "%s<%s: %s>\n",
            $indent,
            str_replace('ju1ius\Pegasus\Expression\\', '', get_class($expr)),
            $expr
        );
    }

    public function leaveExpression(Grammar $grammar, Expression $expr)
    {
        if ($expr instanceof Composite) {
            $this->depth--;
        }
    }
}
