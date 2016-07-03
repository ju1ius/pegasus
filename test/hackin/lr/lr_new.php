<?php

require_once __DIR__.'/../../../vendor/autoload.php';


use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\MetaGrammar;
use ju1ius\Pegasus\Expression\Literal;
use ju1ius\Pegasus\Expression\Reference as Ref;
use ju1ius\Pegasus\Expression\Regex;
use ju1ius\Pegasus\Expression\Sequence;
use ju1ius\Pegasus\Expression\OneOf;
use ju1ius\Pegasus\Expression\OneOrMore;

use ju1ius\Pegasus\Parser;

$syntax = <<<'EOS'

expr    = expr "+" term
        | expr "-" term
        | term

term    = term "*" primary
        | term "/" primary
        | primary

primary = '(' expr ')'
        | num

num     = /[0-9]+/

EOS;

$g = new Grammar();
$g['expr'] = new OneOf([
    new Sequence([
        new Ref('expr'),
        new Literal('+'),
        new Ref('term')
    ], 'plus'),
    new Sequence([
        new Ref('expr'),
        new Literal('-'),
        new Ref('term')
    ], 'minus'),
    new Ref('term')
]);
$g['term'] = new OneOf([
    new Sequence([
        new Ref('term'),
        new Literal('*'),
        new Ref('primary')
    ], 'mul'),
    new Sequence([
        new Ref('term'),
        new Literal('/'),
        new Ref('primary')
    ], 'div'),
    new Ref('primary')
]);
$g['primary'] = new OneOf([
    new Sequence([
        new Literal('('),
        new Ref('expr'),
        new Literal(')')
    ], 'parenthesized'),
    new Ref('num')
]);
$g['num'] = new Regex('[0-9]+');

$g->finalize('expr');


$g = Grammar::fromSyntax($syntax);

echo $g, "\n";
$parser = new Parser\LeftRecursivePackrat($g);
$tree = $parser->parse('(1+(3*2)-7)/9');
//$tree = $parser->parse('12/42+17*3-2');
echo $tree->inspect();
