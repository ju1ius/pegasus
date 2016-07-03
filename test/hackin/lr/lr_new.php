<?php

require_once __DIR__.'/../../../vendor/autoload.php';


use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Parser;

$syntax = <<<'EOS'

expr    <- expr "+" term
        | expr "-" term
        | term

term    <- term "*" primary
        | term "/" primary
        | primary

primary <- '(' expr ')'
        | num

num     <- /[0-9]+/

EOS;

$g1 = Grammar\Builder::create()
    ->rule('expr')->oneOf()
        ->ref('plus')
        ->ref('minus')
        ->ref('term')
    ->rule('plus')->seq()
        ->ref('expr')
        ->literal('+')
        ->ref('term')
    ->rule('minus')->seq()
        ->ref('expr')
        ->literal('-')
        ->ref('term')
    ->rule('term')->oneOf()
        ->ref('multiplication')
        ->ref('division')
        ->ref('primary')
    ->rule('multiplication')->seq()
        ->ref('term')
        ->literal('*')
        ->ref('primary')
    ->rule('division')->seq()
        ->ref('term')
        ->literal('/')
        ->ref('primary')
    ->rule('primary')->oneOf()
        ->ref('parenthesized')
        ->ref('number')
    ->rule('parenthesized')->seq()
        ->literal('(')
        ->ref('expr')
        ->literal(')')
    ->rule('number')
        ->regex('[0-9]+')
    ->getGrammar();


$g2 = Grammar::fromSyntax($syntax);

echo $g2, "\n";
$parser = new Parser\LeftRecursivePackrat($g2);
$tree = $parser->parse('(1+(3*2)-7)/9');
//$tree = $parser->parse('12/42+17*3-2');
echo $tree->inspect();
