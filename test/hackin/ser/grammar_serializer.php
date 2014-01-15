<?php
require_once __DIR__.'/../utils.php';

use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\MetaGrammar;
use ju1ius\Pegasus\Parser\LRPackrat as Parser;
use ju1ius\Pegasus\Visitor\RuleVisitor;
use ju1ius\Pegasus\Visitor\ExpressionTraverser;
use ju1ius\Pegasus\Visitor\RefMaker;

use ju1ius\Pegasus\Expression\Composite;
use ju1ius\Pegasus\Expression\Literal;
use ju1ius\Pegasus\Expression\Regex;
use ju1ius\Pegasus\Expression\Optional;
use ju1ius\Pegasus\Expression\OneOrMore;
use ju1ius\Pegasus\Expression\ZeroOrMore;
use ju1ius\Pegasus\Expression\Quantifier;
use ju1ius\Pegasus\Expression\Not;
use ju1ius\Pegasus\Expression\Lookahead;
use ju1ius\Pegasus\Expression\OneOf;
use ju1ius\Pegasus\Expression\Sequence;
use ju1ius\Pegasus\Expression\Reference;


$syntax = <<<'EOS'

start = foobars
foobars = (foos | bars)*

foos = foos foo | L1:foo
bars = bars bar | L2:bar

foo = "foo"
bar = "bar"

EOS;

//$grammar = Grammar::fromSyntax($syntax);
//echo $grammar, "\n";
//exit();
//$meta = MetaGrammar::create();
//echo "Meta:\n=====\n", "\n", $meta, "\n";
//echo $meta['parenthesized'];
$meta = MetaGrammar::getGrammar();
$parser = new Parser($meta);
$tree = $parser->parseAll($syntax);
list($rules, $default) = (new RuleVisitor)->visit($tree);
$grammar = new Grammar($rules, $default);
$grammar->finalize();

$saved = serialize($grammar);
file_put_contents(__DIR__.'/grammar.ser', $saved);
$restored = unserialize($saved);
echo $restored, "\n";

//$trav = new ExpressionTraverser();
//$vis = new RefMaker($grammar);
//$trav->addVisitor($vis);
//foreach ($grammar as $name => $expr) {
	////$g[$name] = $trav->traverse($expr);
	//$trav->traverse($expr);
//}
//echo $grammar, "\n";

//$saved = serialize($grammar);
//$restored = unserialize($saved);

//echo $restored, "\n";

