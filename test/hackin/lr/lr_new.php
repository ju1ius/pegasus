<?php

require_once __DIR__.'/../../../vendor/autoload.php';


use ju1ius\Pegasus\Expression\Literal;
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

$expr = new OneOf([], 'expr');
$term = new OneOf([], 'term');
$num = new Regex('[0-9]+', 'num');
$paren = new Sequence([
    new Literal('(', 'LPAREN'),
    $expr,
    new Literal(')', 'RPAREN')
], 'paren');
$primary = new OneOf([$paren, $num], 'primary');

$minus = new Sequence([
    $expr,
    new Literal('-', '-'),
    $term
], 'minus');
$plus = new Sequence([
    $expr,
    new Literal('+', '+'),
    $term
], 'plus');

$mul = new Sequence([
    $term,
    new Literal('*', '*'),
    $primary
], 'mul');
$div = new Sequence([
    $term,
    new Literal('/', '/'),
    $primary
], 'div');

$term->members = [$mul, $div, $primary];
$expr->members = [$minus, $plus, $term];

$parser = new Parser\LRPackrat($expr);
$tree = $parser->parse('(1+(3*2)-7)/9');
//$tree = $parser->parse('12/42+17*3-2');
echo $tree->treeview();
