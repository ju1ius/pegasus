<?php
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
