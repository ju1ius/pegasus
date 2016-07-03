<?php

require_once __DIR__.'/../utils.php';

use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Parser\LeftRecursivePackrat as Parser;

$syntax = <<<'EOS'
start = a:"foo" "bar" "${a}"
EOS;

$grammar = Grammar::fromSyntax($syntax);
echo $grammar, "\n";
$parser = new LeftRecursivePackrat($grammar);
$tree = $parser->parse('foobarfoo');
echo $tree->inspect(), "\n";
