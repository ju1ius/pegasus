<?php
require_once __DIR__.'/../../vendor/autoload.php';

use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Parser\LRPackrat as Parser;


$syntax = <<<'EOS'
enclosed = delim /\w+/ "${delim}"
delim = /[~#]+/
EOS;

$grammar = new Grammar($syntax);
$parser = new Parser($grammar);
$tree = $parser->parse('###w00t###');
echo $tree->treeview(), "\n";
