<?php declare(strict_types=1);


use ju1ius\Pegasus\Grammar\Optimizer;
use ju1ius\Pegasus\GrammarBuilder;


$b = GrammarBuilder::create('JSON');
$b->rule('json')->seq()
    ->ref('_')
    ->oneOf()
        ->ref('object')
        ->ref('array')
    ->end()
    ->eof()
;
$b->rule('object')->seq()
    ->cut()->ignore()->seq()
        ->literal('{')
        ->ref('_')
    ->end()
    ->optional()->ref('members')
    ->ignore()->literal('}')->ref('_')
;
$b->rule('members')->seq()
    ->ref('pair')
    ->zeroOrMore()->seq()
        ->ignore()->literal(',')->ref('_')
        ->ref('pair')
;
$b->rule('pair')->seq()
    ->ref('string')
    ->ignore()->literal(':')->ref('_')
    ->ref('value')
;
$b->rule('array')->seq()
    ->cut()->ignore()->seq()
        ->literal('[')
        ->ref('_')
    ->end()
    ->optional()->ref('elements')
    ->ignore()->literal(']')->ref('_')
;
$b->rule('elements')->seq()
    ->ref('value')
    ->zeroOrMore()->seq()
        ->ignore()->literal(',')->ref('_')
        ->ref('value')
;
$b->rule('value')->oneOf()
    ->ref('object')
    ->ref('array')
    ->ref('string')
    ->ref('number')
    ->named('true')->seq()->literal('true')->ref('_')->end()
    ->named('false')->seq()->literal('false')->ref('_')->end()
    ->named('null')->seq()->literal('null')->ref('_')->end()
;

$b->rule('string')->seq()
    ->ignore()->literal('"')
    ->match('(?:\\.|[^"])*')
    ->ignore()->literal('"')
    ->ref('_');
$b->rule('number')->seq()
    ->token()
        ->seq()
            ->ref('int')
            ->optional()->ref('frac')
            ->optional()->ref('expo')
        ->end()
    ->end()
    ->ref('_');

$b->rule('int')->match('-?(?:[1-9]\d*|0(?!\d))');
$b->rule('frac')->match('\.\d+');
$b->rule('expo')->match('[eE][+-]?\d+');
$b->rule('_')->ignore()->match('\s*');

$grammar = $b->getGrammar();
$grammar->inline('_');

$grammar = Optimizer::optimize($grammar, Optimizer::LEVEL_2);
//Debug::dump($grammar);


return $grammar;
