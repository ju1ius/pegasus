<?php

require_once __DIR__.'/../tree/utils.php';

use ju1ius\Pegasus\MetaGrammar;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Expression\OneOf;
use ju1ius\Pegasus\Expression\OneOrMore;
use ju1ius\Pegasus\Expression\Quantifier;
use ju1ius\Pegasus\Expression\Reference as Ref;
use ju1ius\Pegasus\Expression\Sequence;
use ju1ius\Pegasus\Expression\Literal;

use ju1ius\Pegasus\Visitor\RuleVisitor;


class MyVisitor extends RuleVisitor
{
    public function visit_alternative($node, $children)
    {
        return $children[0];
    }
    public function visit_choice_2($node, $children)
    {
        list($alt1, $others) = $children;
        // other can be sequence or term
        if (is_array($others)) {
            $terms = array_merge([$alt1], $others);
        } else {
            $terms = [$alt1, $others];
        }
        return new OneOf($terms);
    }
    public function visit_sequence($node, $children)
    {
        return new Sequence($children); 
    }
}



//$g = MetaGrammar::getGrammar();
$meta = MetaGrammar::getGrammar();
$g = $meta->copy(true)->unfold();
$g['expression'] = new OneOf([
    new Ref('choice_2'), 
    new Ref('sequence'), 
    new Ref('term'), 
]);
// choice v1
$g['choice_1'] = new Sequence([
    new Ref('terms'),
    new OneOrMore([
        new Sequence([
            new Ref('OR'),
            new Ref('terms')
        ])
    ])
]);
// choice v2
$g['choice_2'] = new Sequence([
    new Ref('alternative'),
    new OneOrMore([
        new Sequence([
            new Ref('OR'),
            new Ref('alternative')
        ])
    ])
]);
$g['alternative'] = new OneOf([
    new Ref('sequence'),
    new Ref('term')
]);
$g['sequence'] = new Quantifier([new Ref('term')], '', 2);
//echo $g, "\n";

//$g->finalize();
//echo $g->getStartRule();
//$g = $meta->merge($g);
//$g->setStartRule('grammar');


$syntax = <<<'EOS'
#foo = "a" | 'b' | 'c' | "d"
#foobar = 'foo' 'bar'
foo = 'foo' 'bar' | 'foobar'
EOS;


$tree = parse_syntax($syntax, $g);
//echo $tree->treeview(), "\n";
list($rules, $start) = (new MyVisitor)->visit($tree);
var_dump($rules);

//echo $new, "\n";

//$grammar = grammar_from_tree($tree);
//print_expr_tree($grammar);
