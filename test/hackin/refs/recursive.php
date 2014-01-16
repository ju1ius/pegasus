<?php
require_once __DIR__.'/../../../vendor/autoload.php';

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Literal;
use ju1ius\Pegasus\Expression\OneOf;
use ju1ius\Pegasus\Visitor\ExpressionTraverser;
use ju1ius\Pegasus\Visitor\ExpressionVisitor;

$foobars = new OneOf([], 'foobars');
$foobar = new OneOf([], 'foobar');
$foo = new Literal('foo', 'foo');
$bar = new Literal('bar', 'bar');
$foobar->children = [$foo, $bar];
$foobars->children = [$foobars, $foobar];


class MyVisitor extends ExpressionVisitor
{
    public function enterNode(Expression $expr)
    {
        echo ">>> Entering expression '{$expr->name}' [{$expr->id}]\n";
    }
    public function leaveNode(Expression $expr)
    {
        echo ">>> Leaving expression '{$expr->name}' [{$expr->id}]\n";
    }
}

$trav = new ExpressionTraverser();
$trav->addVisitor(new MyVisitor);
$trav->traverse($foobars);
