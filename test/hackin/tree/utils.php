<?php
require_once __DIR__ . '/../utils.php';

use ju1ius\Pegasus\Debug\ExpressionPrinter;
use ju1ius\Pegasus\Debug\GrammarPrinter;
use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Composite;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\MetaGrammar;
use ju1ius\Pegasus\Parser\LeftRecursivePackrat;
use ju1ius\Pegasus\Traverser\ExpressionTraverser;
use ju1ius\Pegasus\Traverser\GrammarTraverser;
use ju1ius\Pegasus\Visitor\RuleVisitor;

function parse_syntax($syntax, Grammar $g = null)
{
    if (!$g) {
        $g = MetaGrammar::getGrammar();
    }
    $p = new LeftRecursivePackrat($g);

    return $p->parse($syntax);
}

function print_expr(Expression $tree)
{
    $trav = new ExpressionTraverser();
    $trav->addVisitor(new ExpressionPrinter);
    $trav->traverse($tree);
}

function print_grammar(Grammar $grammar)
{
    $trav = new GrammarTraverser(false);
    $trav->addVisitor(new GrammarPrinter);
    $trav->traverse($grammar);
}

function exp_tree_map(Expression $expr, callable $callback, $visited = null)
{
    if (!$visited) {
        $visited = new \SplObjectStorage();
    }
    if ($visited->contains($expr)) {
        return;
    }
    $callback($expr);
    $visited->attach($expr);
    if ($expr instanceof Composite) {
        foreach ($expr as $child) {
            exp_tree_map($child, $callback, $visited);
        }
    }
}
