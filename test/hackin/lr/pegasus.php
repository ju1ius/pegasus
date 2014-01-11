<?php
require_once __DIR__.'/../../../vendor/autoload.php';

use ju1ius\Pegasus\MetaGrammar;
use ju1ius\Pegasus\Parser\LRPackrat as Parser;


$grammar = MetaGrammar::create();
$parser = new Parser($grammar);

$text = <<<'EOS'
rule = "foo" | "bar"
EOS;

$tree = $parser->parse($text);
echo $tree->treeview();
