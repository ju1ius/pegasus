<?php


require_once __DIR__.'/../tree/utils.php';

use ju1ius\Pegasus\MetaGrammar;
use ju1ius\Pegasus\Grammar;

//$mg = MetaGrammar::create();
//$rx = $mg['regex']->children[0]->compiledPattern;
//var_dump($rx);
//preg_match($rx, '/foo\/bar/Sx', $matches);
//var_dump($matches);
//exit();

//$syntax = <<<'EOS'
//#foo = "a" | 'b' | 'c' | "d"
//#foobar = 'foo' 'bar'
//foo = 'foo' bar:( ~'>' 'bar' | 'foobar')
//EOS;

$syntax = file_get_contents(__DIR__.'/pegasus.peg');

$mem_empty = memory_get_usage();

$g = Grammar::fromSyntax($syntax);

echo sprintf("Mem: %s, peak: %s\n", memory_get_usage() - $mem_empty, memory_get_peak_usage() - $mem_empty);

echo "\n\n", $g, "\n\n";
//print_grammar($g);
