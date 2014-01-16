<?php
require_once __DIR__.'/../../../vendor/autoload.php';


use ju1ius\Pegasus\Expression\Literal;
use ju1ius\Pegasus\Expression\Regex;
use ju1ius\Pegasus\Expression\Sequence;
use ju1ius\Pegasus\Expression\OneOf;
use ju1ius\Pegasus\Expression\OneOrMore;

use ju1ius\Pegasus\Parser;
use ju1ius\Pegasus\NodeVisitor;

$syntax = <<<'EOS'
xs  = xs "x"
    | "x"
EOS;

class XXXVisitor extends NodeVisitor
{
    public function visit_x($node, $children)
    {
        return 'X';
    }
    public function visit_xxx($node, $children)
    {
        list($seq) = $children;
        return $seq;
    }
    public function visit_xxx_x($node, $children)
    {
        list($xxx, $x) = $children;
        return $xxx . $x;
    }
    
}


$xxx = new OneOf([], 'xxx');
$x = new Literal('x', 'x');
$xxx->children = [
    new Sequence([$xxx, $x], 'xxx_x'),
    $x
];

$parser = new Parser\LRPackrat($xxx);
$tree = $parser->parse('xxxx');
//echo $tree->inspect();
$visited = (new XXXVisitor)->visit($tree);
var_export($visited);
