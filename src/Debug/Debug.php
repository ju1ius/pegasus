<?php
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Debug;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Node;
use ju1ius\Pegasus\Traverser\ExpressionTraverser;
use ju1ius\Pegasus\Traverser\GrammarTraverser;
use ju1ius\Pegasus\Traverser\NodeTraverser;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
final class Debug
{
    /**
     * @param $value
     */
    public static function dump($value)
    {
        if ($value instanceof Grammar) {
            self::dumpGrammar($value);
        } elseif ($value instanceof Expression) {
            self::dumpExpression($value);
        } elseif ($value instanceof Node) {
            self::dumpNode($value);
        } else {
            var_dump($value);
        }
    }

    /**
     * Prints a string representation of a grammar tree.
     *
     * @param Grammar $grammar
     *
     * @throws Grammar\Exception\SelfReferencingRule
     */
    public static function dumpGrammar(Grammar $grammar)
    {
        $t = new GrammarTraverser(false);
        $t->addVisitor(new GrammarPrinter);
        $t->traverse($grammar);
    }

    /**
     * Prints a string representation of an expression tree.
     *
     * @param Expression $expr
     */
    public static function dumpExpression(Expression $expr)
    {
        $t = new ExpressionTraverser();
        $t->addVisitor(new ExpressionPrinter);
        $t->traverse($expr);
    }

    /**
     * Prints a string representation of a parse tree.
     *
     * @param Node $node
     */
    public static function dumpNode(Node $node)
    {
        $t = new NodeTraverser();
        $t->addVisitor(new ParseTreePrinter(null, false));
        $t->traverse($node);
    }
}
