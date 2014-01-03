<?php

require_once __DIR__.'/../../vendor/autoload.php';

use ju1ius\Pegasus\PegasusGrammar;
use ju1ius\Pegasus\Parser\Packrat as Parser;
use ju1ius\Pegasus\Node\Terminal as Term;
use ju1ius\Pegasus\Node\Composite as Comp;
use ju1ius\Pegasus\Node\Regex as Rx;


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

foreach ($grammar as $rule) {
	echo $rule->asRule(), "\n";
}

