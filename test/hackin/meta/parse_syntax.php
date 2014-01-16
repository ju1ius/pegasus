<?php


require_once __DIR__.'/../tree/utils.php';

use ju1ius\Pegasus\Grammar;

$syntax = <<<'EOS'
#foo = "a" | 'b' | 'c' | "d"
#foobar = 'foo' 'bar'
foo = 'foo' bar:( ~'>' 'bar' | 'foobar')
EOS;

$mem_empty = memory_get_usage();

$g = Grammar::fromSyntax($syntax);

echo sprintf("Mem: %s, peak: %s\n", memory_get_usage() - $mem_empty, memory_get_peak_usage() - $mem_empty);

echo "\n\n", $g, "\n\n";
print_grammar($g);
