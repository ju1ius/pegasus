<?php
require_once __DIR__.'/../tree/utils.php';

use ju1ius\Pegasus\Grammar;

$syntax = <<<'EOS'
#foo = "a" | 'b' | 'c' | "d"
#foobar = 'foo' 'bar'
foo = 'foo' 'bar' | 'foobar'
EOS;

$g = Grammar::fromSyntax($syntax);
echo "\n\n", $g, "\n\n";
print_grammar($g);
