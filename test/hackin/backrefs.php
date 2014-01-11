<?php

$parts = [];
$refs = [];


function split_refs($str)
{
	preg_match_all('
/
	((?: \\. | [^\$] )*?)
	\$\{(\w+)\}
	((?: \\. | [^\$] )*)
/x'
		, $str
		, $matches
		, PREG_SET_ORDER
	);
	return $matches;
}

function replace_refs($matches, $replacements)
{
	$out = '';
	foreach ($matches as $match) {
		list($_, $left, $ref, $right) = $match;
		$out .= $left . $replacements[$ref] . $right;
	}
	return $out;
}

function replace_preg($str, $replacements)
{
	return preg_replace_callback('/\$\{(\w+)\}/', function($matches) use($replacements) {
		return $replacements[$matches[1]];
	}, $str);
}

$test = str_repeat('foo${test}bar', 10);
$parts = split_refs($test);
$replacements = ['test' => '#####'];

$start = microtime(true);
replace_refs($parts, $replacements);
$step1 = microtime(true);
replace_preg($test, $replacements);
$step2 = microtime(true);

echo sprintf("Replace took: %ss\n", ($step1 - $start)*100);
echo sprintf("Replace (pcre) took: %ss\n", ($step2 - $step1)*100);


exit();

require_once __DIR__.'/../../vendor/autoload.php';

use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Parser\LRPackrat as Parser;




$syntax = <<<'EOS'
seq = (seq _ enclosed) | enclosed
enclosed = delim /\w+/ "${delim}"
delim = /[~#]+/
_ = /\s*/
EOS;

$grammar = new Grammar($syntax);
$parser = new Parser($grammar);
$tree = $parser->parse('###w00t### ~omy~');
echo $tree->treeview(), "\n";
