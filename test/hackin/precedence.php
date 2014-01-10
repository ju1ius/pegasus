<?php

require_once __DIR__.'/../../vendor/autoload.php';

use ju1ius\Pegasus\PegasusGrammar;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\RuleVisitor;
use ju1ius\Pegasus\Parser\Packrat as Parser;
use ju1ius\Pegasus\Node\Terminal as Term;
use ju1ius\Pegasus\Node\Composite as Comp;
use ju1ius\Pegasus\Node\Regex as Rx;


$rules = PegasusGrammar::getRules();
$parser = new Parser($rules);
$tree = $parser->parse(PegasusGrammar::SYNTAX);
file_put_contents(__DIR__.'/pegasus_grammar.tree', $tree->treeview());
list($exprs, $start_rule) = (new RuleVisitor)->visit($tree);
foreach ($exprs as $name => $value) {
	echo $value->asRule(), "\n";
}
$g = new Grammar();
foreach ($exprs as $name => $value) {
	$g[$name] = $value;
}
$g->setDefault($start_rule->name);

$rule = "x = 'y' 'z' | 't'";
$parser = new Parser($g);
$tree = $parser->parse(PegasusGrammar::SYNTAX);
file_put_contents(__DIR__.'/test_grammar.tree', $tree->treeview());
//list($exprs, $start_rule) = (new RuleVisitor)->visit($tree);


exit();

$rule = "'y' 'z' | 't'";

$grammar = PegasusGrammar::build();
$parser = new Parser($grammar);
$tree = $parser->parse($rule, 0, 'expression');
echo $tree->treeview(), "\n";

$expected = new Comp('expression', "'y' 'z' | 't'", 0, 12, [
	new Comp('ored', "'y' 'z' | 't'", 0, 12, [
		new Comp('sequence', "'y' 'z' | 't'", 0, 7, []),
		new Comp('sequence', "'y' 'z' | 't'", 10, 12, []),
	])
]);
