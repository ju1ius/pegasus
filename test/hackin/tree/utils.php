<?php
require_once __DIR__.'/../utils.php';

use ju1ius\Pegasus\MetaGrammar;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Composite;

use ju1ius\Pegasus\Traverser\GrammarTraverser;
use ju1ius\Pegasus\Traverser\ExpressionTraverser;
use ju1ius\Pegasus\Visitor\GrammarVisitor;
use ju1ius\Pegasus\Visitor\ExpressionVisitor;

use ju1ius\Pegasus\Parser\Packrat;
use ju1ius\Pegasus\Parser\LRPackrat;
use ju1ius\Pegasus\Visitor\RuleVisitor;

use ju1ius\Pegasus\Debug\ExpressionPrinter;
use ju1ius\Pegasus\Debug\GrammarTreePrinter;


function parse_syntax($syntax, Grammar $g=null)
{
    if (null === $g) $g = MetaGrammar::getGrammar();
    $p = new LRPackrat($g);
    return $p->parse($syntax);
}

function grammar_from_tree($tree)
{
    list($rules, $start) = (new RuleVisitor)->visit($tree);
    $grammar = new Grammar($rules, $start);
    return $grammar;
}

function print_expr($tree)
{
    $trav = new ExpressionTraverser();
    $trav->addVisitor(new ExpressionPrinter);
    $trav->traverse($tree);
}

function print_grammar($grammar)
{
    $trav = new GrammarTraverser(false);
    $trav->addVisitor(new GrammarTreePrinter);
    $trav->traverse($grammar);
}

function exp_tree_map($expr, $callback, $visited=null)
{
    if (null === $visited) {
        $visited = new SplObjectStorage();
    }
    if ($visited->contains($expr)) {
        return;
    }
    $callback($expr);
    $visited->attach($expr);
    if ($expr instanceof Composite) {
        foreach ($expr->members as $member) {
            exp_tree_map($member, $callback, $visited);
        }
    }
}
