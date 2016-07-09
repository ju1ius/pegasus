<?php
require_once __DIR__.'/../../../vendor/autoload.php';

use ju1ius\Pegasus\Grammar\Builder;
use ju1ius\Pegasus\Parser;
use ju1ius\Pegasus\Traverser\NamedNodeTraverser;

$syntax = <<<'EOS'
xs  <- xs "x"
    | "x"
EOS;

class XXXTraverser extends NamedNodeTraverser
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

$g = Builder::create()
    ->rule('xs')->oneOf()
        ->seq()
            ->ref('xs')
            ->literal('x')
        ->end()
        ->literal('x')
    ->getGrammar();

$parser = new Parser\LeftRecursivePackrat($g);
$tree = $parser->parse('xxxx');
//echo $tree->inspect();
$visited = (new XXXTraverser)->traverse($tree);
var_export($visited);
