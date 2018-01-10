<?php declare(strict_types=1);


use ju1ius\Pegasus\Debug\Debug;
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
    ->cut()->skip()->seq()
        ->literal('{')
        ->ref('_')
    ->end()
    ->optional()->ref('members')
    ->skip()->literal('}')->ref('_')
;
$b->rule('members')->seq()
    ->ref('pair')
    ->zeroOrMore()->seq()
        ->skip()->literal(',')->ref('_')
        ->ref('pair')
;
$b->rule('pair')->seq()
    ->ref('string')
    ->skip()->literal(':')->ref('_')
    ->ref('value')
;
$b->rule('array')->seq()
    ->cut()->skip()->seq()
        ->literal('[')
        ->ref('_')
    ->end()
    ->optional()->ref('elements')
    ->skip()->literal(']')->ref('_')
;
$b->rule('elements')->seq()
    ->ref('value')
    ->zeroOrMore()->seq()
        ->skip()->literal(',')->ref('_')
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
    ->skip()->literal('"')
    ->match('(?:\\.|[^"])*')
    ->skip()->literal('"')
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
$b->rule('_')->skip()->match('\s*');

$grammar = $b->getGrammar();
$grammar->inline('_');

$grammar = Optimizer::optimize($grammar, Optimizer::LEVEL_2);
Debug::dump($grammar);


return $grammar;