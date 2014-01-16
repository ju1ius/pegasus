<?php
require_once __DIR__.'/../../../vendor/autoload.php';

use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Expression\Composite;
use ju1ius\Pegasus\Expression\Literal;
use ju1ius\Pegasus\Expression\OneOf;
use ju1ius\Pegasus\Visitor\ExpressionTraverser;
use ju1ius\Pegasus\Visitor\RefMaker;
use ju1ius\Pegasus\Parser\LRPackrat as Parser;


//$foo_l = new Literal('foo');
//$bar_l = new Literal('bar');
//$foobar = new OneOf([$foo_l, $bar_l], 'foobar');
//$foobars = new OneOf([], 'foobars');
//$foobars->children = [$foobars, $foobar];
//$g = new Grammar();
//$g['foobars'] = $foobars;
//$g['foobar'] = $foobar;
//$g->setStartRule('foobars');
//$res = $p->parse('foofoobarbarbarfoobarbarfoo');
//echo $res->inspect(), "\n";


$g = Grammar::fromSyntax('
start = foobars
foobars = foobars | foobar
foobar = foo | bar
foo = "foo"
bar = "bar"
');
$p = new Parser($g);
echo '=====> Before', "\n";
echo $g, "\n";

$trav = new ExpressionTraverser();
$vis = new RefMaker($g);
$trav->addVisitor($vis);
foreach ($g as $name => $expr) {
	//$g[$name] = $trav->traverse($expr);
	$trav->traverse($expr);
}

function test_traversal($expr, $level=0)
{
	$ident = str_repeat('    ', $level);
	if (!$expr instanceof Composite) return;
	foreach ($expr->children as $m) {
	    test_traversal($m, $level+1);
	}
}

echo '=====> After', "\n";
echo $g, "\n";


foreach ($g as $name => $expr) {
	test_traversal($g[$name]);
}
