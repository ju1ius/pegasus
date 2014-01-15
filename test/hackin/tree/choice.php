<?php
require_once __DIR__.'/utils.php';

use ju1ius\Pegasus\MetaGrammar;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Expression\OneOf;
use ju1ius\Pegasus\Expression\OneOrMore;
use ju1ius\Pegasus\Expression\Reference as Ref;
use ju1ius\Pegasus\Expression\Sequence;
use ju1ius\Pegasus\Expression\Literal;

//$g = MetaGrammar::getGrammar();
$g = new Grammar();
$g['expression'] = new OneOf([
    new Ref('choice'), 
    new Ref('sequence'), 
    new Ref('term'), 
]);
$g['choice'] = new Sequence([
    new Ref('terms'),
    new OneOrMore([
        new Sequence([
            new Literal('|'),
            new Ref('_'),
            new Ref('terms')
        ])
    ])
]);
//$g->finalize();
//echo $g->getStartRule();
$meta = MetaGrammar::getGrammar();
$g = $meta->merge($g);
echo $g, "\n";
//$g->setStartRule('grammar');


$syntax = <<<'EOS'
foo = "a" | 'b' | 'c' | "d"
EOS;

$mem_start = memory_get_usage();

$tree = parse_syntax($syntax, $g);
//echo $tree->treeview(), "\n";

$mem_total = memory_get_usage() - $mem_start;
$mem_peak = memory_get_peak_usage();
echo "Memory: $mem_total, $mem_peak\n";

$grammar = grammar_from_tree($tree);
print_expr_tree($grammar);
