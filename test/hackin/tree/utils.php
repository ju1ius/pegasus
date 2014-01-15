<?php
require_once __DIR__.'/../utils.php';

use ju1ius\Pegasus\MetaGrammar;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Composite;
use ju1ius\Pegasus\Visitor\ExpressionTraverser;
use ju1ius\Pegasus\Visitor\ExpressionVisitor;
use ju1ius\Pegasus\Visitor\RuleVisitor;
use ju1ius\Pegasus\Parser\Packrat;
use ju1ius\Pegasus\Parser\LRPackrat;

function parse_syntax($syntax, Grammar $g=null)
{
    if (null === $g) $g = MetaGrammar::getGrammar();
    $p = new LRPackrat($g);
    return $p->parse($syntax);
}


class ExprTreePrinter extends ExpressionVisitor
{
    public function beforeTraverse(Expression $node)
    {
        $this->level = 0;
    }
    private function indent()
    {
        return str_repeat('+---', $this->level);
    }
    public function enterNode(Expression $node)
    {
        $hash = spl_object_hash($node);
        $clash = $node->id !== $hash;
        if ($clash) {
            $hash = sprintf('CLASH: id => %s / hash => %s', $node->id, $hash);
        }
        echo sprintf(
            "%s+<%s(%s) = %s> [id => %s]\n",
            $this->indent(),
            $node->name ? "{$node->name} ": '',
            str_replace('ju1ius\Pegasus\Expression\\', '', get_class($node)),
            $node->asRhs(),
            $hash
        );
        if ($node instanceof Composite) {
            $this->level++;
        }
    }
    public function leaveNode(Expression $node)
    {
        if ($node instanceof Composite) {
            $this->level--;
        }
    }
}


function grammar_from_tree($tree)
{
    list($rules, $start) = (new RuleVisitor)->visit($tree);
    $grammar = new Grammar($rules, $start);
    return $grammar;
}

function print_expr_tree($tree)
{
    $trav = new ExpressionTraverser();
    $trav->addVisitor(new ExprTreePrinter);
    if ($tree instanceof Grammar) {
        foreach ($grammar as $name => $expr) {
            echo "Rule: $name\n====================\n";
            $trav->traverse($expr);
        }
    } else {
        $trav->traverse($tree);
    }
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
