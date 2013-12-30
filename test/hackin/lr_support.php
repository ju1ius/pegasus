<?php

require_once __DIR__.'/../../vendor/autoload.php';

use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Packrat\LRParser;

$syntax = <<<'EOS'
x = expr | num
expr = x '-' num
num = /[0-9]+/
EOS;
$grammar = new Grammar($syntax);
$parser = new LRParser($grammar);
$tree = $parser->parse('1-2-3');
echo $tree->treeview(), "\n";
